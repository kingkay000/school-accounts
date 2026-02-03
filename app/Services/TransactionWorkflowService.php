<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionException;
use Illuminate\Support\Collection;

class TransactionWorkflowService
{
    public function refreshStatus(Transaction $transaction): Transaction
    {
        $transaction->loadMissing(['attachments', 'taxAssessments', 'exceptions', 'purchaseRequests', 'goodsReceivedNotes']);

        $hasAttachments = $transaction->attachments->isNotEmpty();
        $hasPaymentEvidence = $transaction->bank_log_id !== null
            || $transaction->attachments->whereIn('document_type', ['payment', 'receipt'])->isNotEmpty();

        $requiredEvidence = $this->requiredEvidence($transaction);
        $missingEvidence = $this->missingEvidence($transaction, $requiredEvidence, $hasPaymentEvidence);

        $this->syncExceptions($transaction, $missingEvidence);

        $taxAssessment = $transaction->taxAssessments->sortByDesc('created_at')->first();
        $taxAssessed = $taxAssessment && $taxAssessment->status !== 'pending';
        $hasOpenExceptions = $transaction->exceptions()->where('status', 'open')->exists();

        $status = 'captured';
        if ($hasAttachments) {
            $status = 'documented';
        }
        if ($hasPaymentEvidence) {
            $status = 'matched';
        }
        if (empty($missingEvidence) && $hasPaymentEvidence) {
            $status = 'validated';
        }
        if ($status === 'validated' && $taxAssessed) {
            $status = 'tax_assessed';
        }
        if ($status === 'tax_assessed' && !$hasOpenExceptions) {
            $status = 'audit_ready';
        }
        if ($hasOpenExceptions) {
            $status = 'exception';
        }

        $transaction->status = $status;
        $transaction->save();

        return $transaction;
    }

    public function requiredEvidence(Transaction $transaction): Collection
    {
        $requirements = match ($transaction->transaction_type) {
            'goods' => collect(['purchase_request', 'invoice', 'grn']),
            'service' => collect(['invoice', 'completion_report']),
            'asset' => collect(['purchase_request', 'invoice', 'grn', 'asset_registry']),
            default => collect(['invoice']),
        };

        return $requirements;
    }

    protected function missingEvidence(Transaction $transaction, Collection $requiredEvidence, bool $hasPaymentEvidence): array
    {
        $missing = [];

        foreach ($requiredEvidence as $docType) {
            if ($docType === 'purchase_request') {
                $hasPurchaseRequest = $transaction->purchaseRequests()->exists()
                    || $transaction->attachments()->where('document_type', 'purchase_request')->exists();
                if (!$hasPurchaseRequest) {
                    $missing[] = 'missing_purchase_request';
                }
                continue;
            }

            if ($docType === 'grn') {
                $hasGrn = $transaction->goodsReceivedNotes()->exists()
                    || $transaction->attachments()->whereIn('document_type', ['grn', 'delivery_note'])->exists();
                if (!$hasGrn) {
                    $missing[] = 'missing_grn';
                }
                continue;
            }

            if ($docType === 'completion_report') {
                $hasCompletion = $transaction->attachments()->where('document_type', 'completion_report')->exists();
                if (!$hasCompletion) {
                    $missing[] = 'missing_completion_report';
                }
                continue;
            }

            if ($docType === 'asset_registry') {
                $hasAssetRegistry = $transaction->attachments()->where('document_type', 'asset_registry')->exists();
                if (!$hasAssetRegistry) {
                    $missing[] = 'missing_asset_registry';
                }
                continue;
            }

            $hasDocument = $transaction->attachments()->where('document_type', $docType)->exists();
            if (!$hasDocument) {
                $missing[] = 'missing_' . $docType;
            }
        }

        if (!$hasPaymentEvidence) {
            $missing[] = 'missing_payment';
        }

        return $missing;
    }

    protected function syncExceptions(Transaction $transaction, array $missingEvidence): void
    {
        $openExceptions = $transaction->exceptions()->where('status', 'open')->get();
        $requiredTypes = collect($missingEvidence);

        foreach ($requiredTypes as $type) {
            if (!$openExceptions->contains('type', $type)) {
                TransactionException::create([
                    'transaction_id' => $transaction->id,
                    'type' => $type,
                    'severity' => 'medium',
                    'status' => 'open',
                ]);
            }
        }

        foreach ($openExceptions as $exception) {
            if (!$requiredTypes->contains($exception->type)) {
                $exception->update(['status' => 'resolved']);
            }
        }
    }
}
