-- Initial Data Setup for School Accounts Application
-- Database: ace_tax_pro

-- 1. Create Default Admin User
-- Password is 'password'
INSERT INTO users (name, email, password, created_at, updated_at) 
VALUES ('Admin User', 'admin@school.com', '$2y$10$cGNyAiryIajmfz2mUid5COf1Ufcwk9JoYC/8r4XL7goTTqe3hDHJO', NOW(), NOW());

-- 2. Populate Chart of Accounts (Expenses)
INSERT INTO chart_of_accounts (code, name, type, created_at, updated_at) VALUES 
-- A. PERSONNEL & STAFF COSTS (6000 Series)
('6000-01', 'Teachers’ Salaries', 'expense', NOW(), NOW()),
('6000-02', 'Non-Teaching Staff Salaries', 'expense', NOW(), NOW()),
('6000-03', 'Overtime & Extra Lessons Pay', 'expense', NOW(), NOW()),
('6000-04', 'Contract / Casual Staff Wages', 'expense', NOW(), NOW()),
('6000-05', 'Pension Employer Contribution', 'expense', NOW(), NOW()),
('6000-06', 'NHF Contribution', 'expense', NOW(), NOW()),
('6000-07', 'NSITF Contribution', 'expense', NOW(), NOW()),
('6000-08', 'Staff Allowances (Housing, Transport)', 'expense', NOW(), NOW()),
('6000-09', 'Staff Medical Expenses', 'expense', NOW(), NOW()),
('6000-10', 'Staff Training & Development', 'expense', NOW(), NOW()),

-- B. ACADEMIC & INSTRUCTIONAL EXPENSES (6100 Series)
('6100-01', 'Teaching Materials & Textbooks', 'expense', NOW(), NOW()),
('6100-02', 'Laboratory Consumables', 'expense', NOW(), NOW()),
('6100-03', 'Examination Materials', 'expense', NOW(), NOW()),
('6100-04', 'Curriculum Development', 'expense', NOW(), NOW()),
('6100-05', 'E-Learning & Software Licenses', 'expense', NOW(), NOW()),
('6100-06', 'Library Books & Journals', 'expense', NOW(), NOW()),
('6100-07', 'Student Assessment Costs', 'expense', NOW(), NOW()),
('6100-08', 'Practical & Workshop Supplies', 'expense', NOW(), NOW()),

-- C. ADMINISTRATIVE & OFFICE EXPENSES (6200 Series)
('6200-01', 'Office Stationery', 'expense', NOW(), NOW()),
('6200-02', 'Printing & Photocopying', 'expense', NOW(), NOW()),
('6200-03', 'Internet & Data Subscription', 'expense', NOW(), NOW()),
('6200-04', 'Telephone & Communication', 'expense', NOW(), NOW()),
('6200-05', 'Courier & Postage', 'expense', NOW(), NOW()),
('6200-06', 'Office Cleaning Supplies', 'expense', NOW(), NOW()),
('6200-07', 'Office Repairs & Maintenance', 'expense', NOW(), NOW()),

-- D. UTILITIES & FACILITY COSTS (6300 Series)
('6300-01', 'Electricity (PHCN)', 'expense', NOW(), NOW()),
('6300-02', 'Generator Fuel', 'expense', NOW(), NOW()),
('6300-03', 'Generator Repairs', 'expense', NOW(), NOW()),
('6300-04', 'Water Supply', 'expense', NOW(), NOW()),
('6300-05', 'Waste Disposal', 'expense', NOW(), NOW()),
('6300-06', 'Security Services', 'expense', NOW(), NOW()),
('6300-07', 'Facility Cleaning & Janitorial', 'expense', NOW(), NOW()),

-- E. STUDENT WELFARE & ACTIVITIES (6400 Series)
('6400-01', 'Boarding Feeding & Kitchen Costs', 'expense', NOW(), NOW()),
('6400-02', 'Sports & Recreation', 'expense', NOW(), NOW()),
('6400-03', 'Excursions & Educational Trips', 'expense', NOW(), NOW()),
('6400-04', 'Student Health & First Aid', 'expense', NOW(), NOW()),
('6400-05', 'Guidance & Counselling', 'expense', NOW(), NOW()),
('6400-06', 'Student Competitions', 'expense', NOW(), NOW()),

-- F. MAINTENANCE & INFRASTRUCTURE (6500 Series)
('6500-01', 'Building Repairs', 'expense', NOW(), NOW()),
('6500-02', 'Furniture Repairs', 'expense', NOW(), NOW()),
('6500-03', 'Electrical Repairs', 'expense', NOW(), NOW()),
('6500-04', 'Plumbing Repairs', 'expense', NOW(), NOW()),
('6500-05', 'ICT Equipment Repairs', 'expense', NOW(), NOW()),
('6500-06', 'Grounds & Landscaping', 'expense', NOW(), NOW()),

-- G. TRANSPORT & LOGISTICS (6600 Series)
('6600-01', 'School Bus Fuel', 'expense', NOW(), NOW()),
('6600-02', 'Bus Maintenance', 'expense', NOW(), NOW()),
('6600-03', 'Vehicle Insurance', 'expense', NOW(), NOW()),
('6600-04', 'Driver Allowances', 'expense', NOW(), NOW()),
('6600-05', 'Vehicle Licensing & Road Worthiness', 'expense', NOW(), NOW()),

-- H. PROFESSIONAL, LEGAL & COMPLIANCE COSTS (6700 Series)
('6700-01', 'Audit Fees', 'expense', NOW(), NOW()),
('6700-02', 'Legal Fees', 'expense', NOW(), NOW()),
('6700-03', 'Consultancy Fees', 'expense', NOW(), NOW()),
('6700-04', 'Tax Advisory Fees', 'expense', NOW(), NOW()),
('6700-05', 'Accreditation & Inspection Fees', 'expense', NOW(), NOW()),
('6700-06', 'Regulatory Levies', 'expense', NOW(), NOW()),

-- I. FINANCE & STATUTORY TAX EXPENSES (6800 Series)
('6800-01', 'Bank Charges', 'expense', NOW(), NOW()),
('6800-02', 'Loan Interest', 'expense', NOW(), NOW()),
('6800-03', 'PAYE Expense', 'expense', NOW(), NOW()),
('6800-04', 'WHT Expense', 'expense', NOW(), NOW()),
('6800-05', 'VAT Expense (Non-recoverable)', 'expense', NOW(), NOW()),
('6800-06', 'Penalties & Fines (Non-allowable)', 'expense', NOW(), NOW()),

-- J. MARKETING & COMMUNICATION (6900 Series)
('6900-01', 'Advertising & Promotions', 'expense', NOW(), NOW()),
('6900-02', 'Website & Social Media', 'expense', NOW(), NOW()),
('6900-03', 'Prospectus & Branding', 'expense', NOW(), NOW()),
('6900-04', 'Admission Campaigns', 'expense', NOW(), NOW())
;
