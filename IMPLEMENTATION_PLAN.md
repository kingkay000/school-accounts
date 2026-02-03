# Automation-First Audit-Ready System (Nigeria Tax Act 2025)

## Goal
Build an automation-first bookkeeping workflow where every expense becomes **Audit-Ready** only when all evidence, approvals, and tax treatments are validated. The application is the system of record and follow-up engine; **n8n handles document ingestion/extraction from Google Drive** and sends structured payloads to the app. This plan turns the current matching approach into a **transaction case-file state machine** designed for Nigeria Tax Act 2025 requirements.

---

## Operating Model (End-to-End)
1. **User drops documents into Google Drive** (invoice, receipt, GRN, completion report, PR, etc.).
2. **n8n extracts data** (OCR + classification + vendor identification) and calls the app APIs.
3. **The app creates/updates a Transaction Case File** with links to documents, vendor, taxes, and approvals.
4. **State machine evaluates completeness**:
   - Missing evidence → exception queue.
   - Valid tax assessment → tax_assessed.
   - All rules satisfied → audit_ready.
5. **Users act on exception and approval queues** until the transaction is closed.

---

## Architecture Shift: Transaction Case File
All expenditure evidence and validations are anchored to a central **transactions** record. This record is the authoritative “case file” that accumulates documents, tax assessments, approvals, and exceptions until audit-ready.

### State Machine (Transaction Status)
**captured → documented → matched → validated → tax_assessed → audit_ready → closed**  
**exception** can be entered from any state when a rule fails.

**Rules:**
- No transition to **audit_ready** until **tax_assessed** and no open exceptions.
- **matched** requires confirmed linkage to a bank log or payment evidence.
- **validated** requires all document requirements for the transaction type.
- **tax_assessed** requires VAT/WHT logic completed and assessment approved.

---

## Data Model (New & Modified)

### [NEW] transactions (Anchor)
| Field | Notes |
| --- | --- |
| id, bank_log_id (nullable), direction (in/out), amount, txn_date | Core case file identity. |
| counterparty_name, narration | Primary descriptors. |
| transaction_type (goods/service/asset/other) | Drives validation rules. |
| status (captured, documented, matched, validated, tax_assessed, audit_ready, exception, closed) | State machine. |
| risk_flags (json) | Holds fraud or compliance flags. |
| created_at, updated_at | Standard. |

### [NEW] vendors (Master Data)
| Field | Notes |
| --- | --- |
| legal_name, tin, vat_registered | Tax compliance baseline. |
| category (goods/service/mixed), bank_details (json) | Classification and remittance. |

### [NEW] tax_assessments (Tax Control)
| Field | Notes |
| --- | --- |
| transaction_id | Link to case file. |
| vat_applicable, vat_amount | VAT logic output. |
| wht_applicable, wht_rate, wht_amount | WHT logic output. |
| status (pending, withheld, remitted), notes | Compliance lifecycle. |

### [NEW] exceptions (Queue)
| Field | Notes |
| --- | --- |
| transaction_id | Case file linkage. |
| type (missing_doc, date_mismatch, missing_gl, tax_incomplete, etc.) | Root cause. |
| severity, status, resolved_by | Queue management. |

### [NEW] approvals (Audit Trail)
| Field | Notes |
| --- | --- |
| target_type (transaction/pr), target_id | What was approved. |
| approver_id, action, reason | Accountability. |
| evidence_attachment_id | Supporting proof. |

### [MODIFY] attachments (Document Store)
Add: transaction_id (fk), vendor_id (fk), document_type (invoice, receipt, delivery_note, grn, completion_report, other)  
Add: metadata (json: invoice_no, dates, vat_amount, extracted totals), file_hash

### [MODIFY] purchase_requests & goods_received_notes
Add: requested_by/received_by, timestamps, transaction_id linkage.

---

## API Surface (Automation-First)
These endpoints support n8n’s document ingestion and the internal rules engine.

### DocumentController
- **POST /documents**  
  Accepts extracted payload + file link. Creates/updates attachment, detects vendor, and creates/updates transaction.
- **POST /documents/{id}/classify**  
  Updates document_type, vendor_id, or metadata when reclassified.

### MatchingController
- **POST /matching/confirm**  
  Confirms the linkage between bank logs, documents, and transaction case files.
  - Requires: amount, date, vendor hints.
  - Produces: `matched` state or exception (e.g., amount mismatch).

### ValidationController (Rules Engine)
Runs required evidence checks based on transaction_type:
- **Goods** → PR + Invoice + Payment + GRN
- **Service** → Invoice + Payment + Completion Report
- **Asset** → Goods rule + Asset Registry entry
- **Advance Payment** → Prepaid state until GRN/Completion arrives

### TaxController
Evaluates VAT/WHT using vendor data and transaction metadata:
- Generate tax_assessments records.
- Submit for approval or auto-approve by policy.

### ExceptionController
Creates, resolves, and escalates exceptions:
- Missing documentation
- Tax incomplete
- Date mismatch
- Unmatched payment

---

## Nigeria Tax Act 2025 Alignment (Policy Layer)
**Policy engine should encode:**
- VAT applicability based on vendor VAT registration and service/goods category.
- WHT applicability and rate based on vendor type, service type, and residency.
- Auto-flag thresholds for large or unusual transactions (risk_flags).

**Audit-ready definition (minimum):**
1. Complete evidence set per transaction_type.
2. Bank evidence or payment confirmation linked.
3. Tax assessment created and approved (VAT/WHT).
4. No open exceptions.
5. Approval trail is complete for PR and overrides.

---

## UI/UX (Exception-Driven Console)

### Dashboard: Action Items
**View:** `resources/views/dashboard/action_items.blade.php`
- Exception queue grouped by type (“Missing GRN”, “Unmatched Receipt”).
- Tax actions (pending WHT remittance, VAT validation).
- Approval tasks (PRs, overrides).

### Audit Bundle (Case File View)
**View:** `resources/views/audit/bundle.blade.php`
- Transaction summary and status timeline.
- Linked documents gallery.
- Approval history.
- Tax assessment status.

---

## Event Flow (Example)
1. Invoice uploaded → n8n extracts data → `/documents` creates transaction (documented).
2. Bank log arrives → `/matching/confirm` links payment → status becomes matched.
3. Validation runs → missing GRN → exception created.
4. GRN uploaded → exception resolved → status becomes validated.
5. Tax assessment generated + approved → status becomes audit_ready.

---

## Verification Plan

### Automated Tests
- **State machine**: block audit_ready unless tax_assessed and exceptions cleared.
- **Matching logic**: verify candidate selection by amount/date/vendor.
- **Tax rules**: VAT/WHT applicability per vendor + transaction type.

### Manual Verification
End-to-end flow:
1. Upload invoice (Google Drive) → Transaction documented.
2. Upload bank log → matched.
3. Missing GRN triggers exception.
4. Upload GRN → validated.
5. Tax assessment completed → audit_ready.

---

## Implementation Phasing (Suggested)
1. **Data model + migrations**: transactions, tax_assessments, exceptions, approvals, attachment updates.
2. **API endpoints**: documents, matching, validation, tax, exceptions.
3. **Rules engine**: state transitions + Nigeria tax policy layer.
4. **UI**: action items dashboard + audit bundle view.
5. **Testing + scripts**: automated tests + seed data for verification.
