-- =====================================================
-- SDBIP & IDP Management System - Sample Data
-- South African Municipality Sample Dataset
-- =====================================================

USE sdbip_idp;

-- =====================================================
-- FINANCIAL YEARS (SA Municipal: July - June)
-- =====================================================
INSERT INTO financial_years (year_label, start_date, end_date, status, is_current) VALUES
('2023/2024', '2023-07-01', '2024-06-30', 'closed', 0),
('2024/2025', '2024-07-01', '2025-06-30', 'active', 1),
('2025/2026', '2025-07-01', '2026-06-30', 'planning', 0);

-- =====================================================
-- DIRECTORATES
-- =====================================================
INSERT INTO directorates (name, code, description, budget_allocation) VALUES
('Office of the Municipal Manager', 'OMM', 'Executive management, strategic planning, internal audit, and performance management', 45000000.00),
('Corporate Services', 'CORP', 'Human resources, ICT, legal services, and administrative support', 85000000.00),
('Financial Services', 'FIN', 'Budget management, revenue, expenditure, supply chain, and financial reporting', 120000000.00),
('Infrastructure and Technical Services', 'INFRA', 'Roads, water, sanitation, electricity, and project management', 350000000.00),
('Community Services', 'COMM', 'Libraries, parks, cemeteries, environmental health, and social development', 95000000.00),
('Economic Development and Planning', 'ECON', 'LED, spatial planning, building control, and tourism', 55000000.00);

-- =====================================================
-- USERS (Sample with hashed passwords - password is 'password123')
-- =====================================================
-- Password hash for 'password123': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO users (username, email, password_hash, first_name, last_name, role, directorate_id, employee_number, job_title) VALUES
-- System Admin
('admin', 'admin@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', NULL, 'ADMIN001', 'System Administrator'),

-- Municipal Manager & Directors
('mm.ndlovu', 'mm@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thabo', 'Ndlovu', 'director', 1, 'MM001', 'Municipal Manager'),
('dir.corp.mokoena', 'corp.director@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nomvula', 'Mokoena', 'director', 2, 'DIR002', 'Director: Corporate Services'),
('cfo.dlamini', 'cfo@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sipho', 'Dlamini', 'director', 3, 'DIR003', 'Chief Financial Officer'),
('dir.infra.nkosi', 'infra.director@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mandla', 'Nkosi', 'director', 4, 'DIR004', 'Director: Infrastructure'),
('dir.comm.zulu', 'comm.director@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lindiwe', 'Zulu', 'director', 5, 'DIR005', 'Director: Community Services'),
('dir.econ.mbeki', 'econ.director@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Mbeki', 'director', 6, 'DIR006', 'Director: Economic Development'),

-- Managers
('mgr.hr.sithole', 'hr.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Sithole', 'manager', 2, 'MGR001', 'Manager: Human Resources'),
('mgr.ict.van.wyk', 'ict.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Johan', 'Van Wyk', 'manager', 2, 'MGR002', 'Manager: ICT'),
('mgr.revenue.pillay', 'revenue.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Pillay', 'manager', 3, 'MGR003', 'Manager: Revenue'),
('mgr.scm.khumalo', 'scm.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bongani', 'Khumalo', 'manager', 3, 'MGR004', 'Manager: Supply Chain'),
('mgr.roads.smith', 'roads.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Smith', 'manager', 4, 'MGR005', 'Manager: Roads & Stormwater'),
('mgr.water.cele', 'water.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Themba', 'Cele', 'manager', 4, 'MGR006', 'Manager: Water & Sanitation'),
('mgr.parks.williams', 'parks.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Williams', 'manager', 5, 'MGR007', 'Manager: Parks & Recreation'),
('mgr.led.ngwenya', 'led.manager@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zanele', 'Ngwenya', 'manager', 6, 'MGR008', 'Manager: LED'),

-- Employees
('emp.hr1.jones', 'hr1@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Jones', 'employee', 2, 'EMP001', 'HR Officer'),
('emp.fin1.naidoo', 'fin1@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kavitha', 'Naidoo', 'employee', 3, 'EMP002', 'Accountant'),
('emp.infra1.botha', 'infra1@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pieter', 'Botha', 'employee', 4, 'EMP003', 'Civil Engineer'),
('emp.comm1.mthembu', 'comm1@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sibongile', 'Mthembu', 'employee', 5, 'EMP004', 'Community Officer'),
('emp.econ1.govender', 'econ1@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Raj', 'Govender', 'employee', 6, 'EMP005', 'Planning Officer'),

-- Independent Assessors
('assess.mahlangu', 'assessor1@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thandi', 'Mahlangu', 'independent_assessor', NULL, 'ASS001', 'Performance Assessor'),
('assess.venter', 'assessor2@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Willem', 'Venter', 'independent_assessor', NULL, 'ASS002', 'Performance Assessor'),
('assess.maharaj', 'assessor3@municipality.gov.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anil', 'Maharaj', 'independent_assessor', NULL, 'ASS003', 'Performance Assessor');

-- Update directorate heads
UPDATE directorates SET head_user_id = 2 WHERE id = 1;
UPDATE directorates SET head_user_id = 3 WHERE id = 2;
UPDATE directorates SET head_user_id = 4 WHERE id = 3;
UPDATE directorates SET head_user_id = 5 WHERE id = 4;
UPDATE directorates SET head_user_id = 6 WHERE id = 5;
UPDATE directorates SET head_user_id = 7 WHERE id = 6;

-- =====================================================
-- DEPARTMENTS
-- =====================================================
INSERT INTO departments (directorate_id, name, code, manager_id) VALUES
-- Corporate Services Departments
(2, 'Human Resources', 'HR', 8),
(2, 'Information & Communication Technology', 'ICT', 9),
(2, 'Legal Services', 'LEGAL', NULL),
(2, 'Administration', 'ADMIN', NULL),
-- Financial Services Departments
(3, 'Revenue Management', 'REV', 10),
(3, 'Supply Chain Management', 'SCM', 11),
(3, 'Budget & Reporting', 'BUDGET', NULL),
(3, 'Expenditure', 'EXP', NULL),
-- Infrastructure Departments
(4, 'Roads & Stormwater', 'ROADS', 12),
(4, 'Water & Sanitation', 'WATER', 13),
(4, 'Electricity', 'ELEC', NULL),
(4, 'Project Management Unit', 'PMU', NULL),
-- Community Services Departments
(5, 'Parks & Recreation', 'PARKS', 14),
(5, 'Libraries', 'LIB', NULL),
(5, 'Environmental Health', 'ENVH', NULL),
-- Economic Development Departments
(6, 'Local Economic Development', 'LED', 15),
(6, 'Spatial Planning', 'PLAN', NULL);

-- =====================================================
-- STRATEGIC OBJECTIVES (For FY 2024/2025)
-- =====================================================
INSERT INTO idp_strategic_objectives (financial_year_id, objective_code, objective_name, description, national_priority_alignment, directorate_id, weight, created_by) VALUES
-- Office of the MM
(2, 'SO-OMM-01', 'Strengthen governance and institutional capacity', 'Enhance municipal governance through improved oversight, compliance, and institutional development', 'NDP Priority 7: Building a capable state', 1, 15.00, 2),
(2, 'SO-OMM-02', 'Improve organizational performance management', 'Implement effective performance management systems aligned with MFMA and MSA requirements', 'NDP Priority 7: Building a capable state', 1, 10.00, 2),

-- Corporate Services
(2, 'SO-CORP-01', 'Develop and retain skilled workforce', 'Attract, develop and retain skilled personnel to meet service delivery requirements', 'NDP Priority 7: Building a capable state', 2, 12.00, 3),
(2, 'SO-CORP-02', 'Modernize ICT infrastructure', 'Implement smart city technologies and digital transformation initiatives', 'NDP Priority 4: Economic infrastructure', 2, 8.00, 3),

-- Financial Services
(2, 'SO-FIN-01', 'Ensure financial sustainability', 'Maintain sound financial management and improve revenue collection', 'NDP Priority 7: Building a capable state', 3, 15.00, 4),
(2, 'SO-FIN-02', 'Strengthen supply chain management', 'Ensure transparent, fair, and competitive procurement processes', 'NDP Priority 7: Building a capable state', 3, 10.00, 4),

-- Infrastructure
(2, 'SO-INFRA-01', 'Expand and maintain road infrastructure', 'Improve road network and stormwater management systems', 'NDP Priority 4: Economic infrastructure', 4, 12.00, 5),
(2, 'SO-INFRA-02', 'Ensure reliable water and sanitation services', 'Provide sustainable water and sanitation infrastructure', 'NDP Priority 4: Economic infrastructure', 4, 15.00, 5),
(2, 'SO-INFRA-03', 'Improve electricity distribution', 'Enhance electricity infrastructure and reduce losses', 'NDP Priority 4: Economic infrastructure', 4, 10.00, 5),

-- Community Services
(2, 'SO-COMM-01', 'Enhance community facilities and services', 'Improve parks, libraries, and recreational facilities', 'NDP Priority 6: Building inclusive communities', 5, 8.00, 6),
(2, 'SO-COMM-02', 'Promote public health and safety', 'Strengthen environmental health services and community safety', 'NDP Priority 2: Health', 5, 7.00, 6),

-- Economic Development
(2, 'SO-ECON-01', 'Promote local economic development', 'Create an enabling environment for business growth and job creation', 'NDP Priority 3: Economic transformation', 6, 10.00, 7),
(2, 'SO-ECON-02', 'Ensure sustainable land use planning', 'Implement effective spatial planning and land use management', 'NDP Priority 5: Environmental sustainability', 6, 8.00, 7);

-- =====================================================
-- KEY PERFORMANCE INDICATORS
-- =====================================================
INSERT INTO kpis (strategic_objective_id, kpi_code, kpi_name, description, unit_of_measure, baseline, annual_target, q1_target, q2_target, q3_target, q4_target, sla_category, budget_required, budget_allocated, responsible_user_id, directorate_id, data_source, is_strategic, created_by) VALUES

-- OMM KPIs
(1, 'KPI-OMM-001', 'Percentage of council resolutions implemented', 'Track implementation of council decisions', 'Percentage', '75%', '90%', '85%', '87%', '89%', '90%', 'internal_control', 0, 0, 2, 1, 'Council Resolution Register', 1, 2),
(1, 'KPI-OMM-002', 'Audit outcome achieved', 'Achieve unqualified audit opinion from AG', 'Category', 'Qualified', 'Unqualified', NULL, NULL, NULL, 'Unqualified', 'internal_control', 500000, 500000, 2, 1, 'AG Audit Report', 1, 2),
(1, 'KPI-OMM-003', 'Percentage of risk register items addressed', 'Mitigate identified organizational risks', 'Percentage', '60%', '80%', '70%', '75%', '78%', '80%', 'internal_control', 0, 0, 2, 1, 'Risk Register', 1, 2),
(2, 'KPI-OMM-004', 'Percentage of SDBIP targets achieved', 'Overall organizational performance against SDBIP', 'Percentage', '72%', '85%', '80%', '82%', '84%', '85%', 'internal_control', 0, 0, 2, 1, 'Quarterly Performance Reports', 1, 2),

-- Corporate Services KPIs
(3, 'KPI-CORP-001', 'Vacancy rate', 'Percentage of funded posts filled', 'Percentage', '18%', '10%', '15%', '13%', '11%', '10%', 'hr_vacancy', 2000000, 2000000, 8, 2, 'HR System', 1, 3),
(3, 'KPI-CORP-002', 'Staff turnover rate', 'Percentage of staff leaving the organization', 'Percentage', '12%', '8%', '10%', '9%', '9%', '8%', 'hr_vacancy', 0, 0, 8, 2, 'HR System', 0, 3),
(3, 'KPI-CORP-003', 'Training spend as % of payroll', 'Investment in employee development', 'Percentage', '0.8%', '1.5%', '1.0%', '1.2%', '1.4%', '1.5%', 'budget', 1500000, 1500000, 8, 2, 'Training Records', 0, 3),
(4, 'KPI-CORP-004', 'ICT system uptime', 'Availability of critical systems', 'Percentage', '95%', '99%', '97%', '98%', '98%', '99%', 'budget', 3000000, 2500000, 9, 2, 'ICT Monitoring System', 1, 3),
(4, 'KPI-CORP-005', 'Number of e-services implemented', 'Digital services available to residents', 'Number', '5', '12', '7', '9', '11', '12', 'budget', 5000000, 4500000, 9, 2, 'E-Services Portal', 0, 3),

-- Financial Services KPIs
(5, 'KPI-FIN-001', 'Revenue collection rate', 'Percentage of billed revenue collected', 'Percentage', '82%', '92%', '88%', '89%', '91%', '92%', 'internal_control', 500000, 500000, 10, 3, 'Financial System', 1, 4),
(5, 'KPI-FIN-002', 'Debt collection rate', 'Recovery of outstanding debts', 'Percentage', '45%', '60%', '50%', '54%', '57%', '60%', 'internal_control', 800000, 800000, 10, 3, 'Debtors Report', 1, 4),
(5, 'KPI-FIN-003', 'Cost coverage ratio', 'Ratio of revenue to operating costs', 'Ratio', '0.95', '1.05', '0.98', '1.00', '1.02', '1.05', 'budget', 0, 0, 4, 3, 'Financial Statements', 1, 4),
(6, 'KPI-FIN-004', 'SCM compliance rate', 'Percentage of procurement following SCM policy', 'Percentage', '85%', '98%', '92%', '94%', '96%', '98%', 'internal_control', 0, 0, 11, 3, 'SCM Reports', 1, 4),
(6, 'KPI-FIN-005', 'Average procurement turnaround time', 'Days from requisition to award', 'Days', '90', '45', '75', '65', '55', '45', 'internal_control', 0, 0, 11, 3, 'SCM System', 0, 4),
(5, 'KPI-FIN-006', 'Capital budget expenditure rate', 'Percentage of capital budget spent', 'Percentage', '68%', '95%', '20%', '45%', '70%', '95%', 'budget', 0, 0, 4, 3, 'Financial System', 1, 4),

-- Infrastructure KPIs
(7, 'KPI-INFRA-001', 'Kilometers of roads maintained', 'Road maintenance program delivery', 'Kilometers', '120', '180', '45', '90', '135', '180', 'budget', 45000000, 42000000, 12, 4, 'Roads Maintenance Register', 1, 5),
(7, 'KPI-INFRA-002', 'Potholes repaired within 48 hours', 'Responsiveness to road defects', 'Percentage', '65%', '90%', '75%', '80%', '85%', '90%', 'budget', 5000000, 5000000, 12, 4, 'Call Centre Reports', 1, 5),
(7, 'KPI-INFRA-003', 'Stormwater drainage systems cleared', 'Preventive maintenance of drainage', 'Percentage', '70%', '95%', '80%', '85%', '90%', '95%', 'budget', 8000000, 7500000, 12, 4, 'Maintenance Reports', 0, 5),
(8, 'KPI-INFRA-004', 'Water quality compliance', 'Compliance with SANS 241 standards', 'Percentage', '92%', '100%', '95%', '97%', '99%', '100%', 'internal_control', 2000000, 2000000, 13, 4, 'Water Quality Reports', 1, 5),
(8, 'KPI-INFRA-005', 'Water losses percentage', 'Non-revenue water reduction', 'Percentage', '35%', '25%', '32%', '30%', '27%', '25%', 'budget', 15000000, 12000000, 13, 4, 'Water Balance Reports', 1, 5),
(8, 'KPI-INFRA-006', 'Sewer blockages cleared within 24 hours', 'Response to sewer emergencies', 'Percentage', '75%', '95%', '82%', '87%', '91%', '95%', 'budget', 3000000, 3000000, 13, 4, 'Call Centre Reports', 0, 5),
(9, 'KPI-INFRA-007', 'Electricity distribution losses', 'Reduction in technical and non-technical losses', 'Percentage', '18%', '12%', '16%', '15%', '13%', '12%', 'budget', 25000000, 20000000, 5, 4, 'Electricity Reports', 1, 5),
(9, 'KPI-INFRA-008', 'Power outages resolved within 4 hours', 'Response to electricity faults', 'Percentage', '70%', '90%', '75%', '80%', '85%', '90%', 'budget', 5000000, 5000000, 5, 4, 'Outage Reports', 0, 5),

-- Community Services KPIs
(10, 'KPI-COMM-001', 'Parks maintained to standard', 'Percentage of parks meeting maintenance standards', 'Percentage', '65%', '85%', '72%', '77%', '81%', '85%', 'budget', 12000000, 10000000, 14, 5, 'Parks Audit Reports', 1, 6),
(10, 'KPI-COMM-002', 'Library membership increase', 'Growth in active library users', 'Percentage', '5%', '15%', '8%', '10%', '13%', '15%', 'budget', 2000000, 1800000, 6, 5, 'Library System', 0, 6),
(10, 'KPI-COMM-003', 'Community halls utilization rate', 'Usage of community facilities', 'Percentage', '45%', '70%', '52%', '58%', '64%', '70%', 'budget', 1500000, 1500000, 6, 5, 'Booking System', 0, 6),
(11, 'KPI-COMM-004', 'Food premises inspected', 'Health inspections conducted', 'Percentage', '60%', '90%', '70%', '78%', '84%', '90%', 'hr_vacancy', 500000, 500000, 6, 5, 'EH Inspection Reports', 1, 6),
(11, 'KPI-COMM-005', 'Waste collection coverage', 'Households receiving weekly collection', 'Percentage', '92%', '98%', '94%', '95%', '97%', '98%', 'budget', 35000000, 33000000, 6, 5, 'Waste Management Reports', 1, 6),

-- Economic Development KPIs
(12, 'KPI-ECON-001', 'SMMEs supported', 'Number of SMMEs receiving support', 'Number', '150', '300', '75', '150', '225', '300', 'budget', 8000000, 7000000, 15, 6, 'LED Database', 1, 7),
(12, 'KPI-ECON-002', 'Jobs created through LED initiatives', 'Employment creation', 'Number', '500', '1200', '300', '600', '900', '1200', 'budget', 5000000, 5000000, 15, 6, 'LED Reports', 1, 7),
(12, 'KPI-ECON-003', 'Investment facilitated', 'Value of investments attracted', 'Rand (Millions)', '50', '150', '30', '75', '110', '150', 'budget', 2000000, 2000000, 7, 6, 'Investment Reports', 1, 7),
(13, 'KPI-ECON-004', 'Building plans approved within 30 days', 'Turnaround time for approvals', 'Percentage', '55%', '85%', '65%', '72%', '79%', '85%', 'hr_vacancy', 0, 0, 7, 6, 'Building Control System', 1, 7),
(13, 'KPI-ECON-005', 'Land use applications processed', 'Processing of development applications', 'Percentage', '60%', '90%', '70%', '77%', '84%', '90%', 'hr_vacancy', 0, 0, 7, 6, 'Planning System', 0, 7);

-- =====================================================
-- QUARTERLY ACTUALS (Sample data for Q1 and Q2)
-- =====================================================
INSERT INTO kpi_quarterly_actuals (kpi_id, quarter, financial_year_id, target_value, actual_value, variance, achievement_status, self_rating, self_comments, self_submitted_at, self_submitted_by, manager_rating, manager_comments, manager_reviewed_at, manager_user_id, independent_rating, independent_comments, independent_reviewed_at, independent_user_id, aggregated_rating, status) VALUES
-- Q1 Results (Fully assessed)
(1, 1, 2, '85%', '87%', 2.35, 'achieved', 4, 'Exceeded target with improved follow-up processes', '2024-10-10', 2, 4, 'Good progress noted', '2024-10-15', 3, 4, 'Target exceeded', '2024-10-20', 22, 4.00, 'approved'),
(2, 1, 2, NULL, 'In Progress', NULL, 'pending', 3, 'Audit preparation ongoing', '2024-10-10', 2, 3, 'On track', '2024-10-15', 3, 3, 'Acceptable progress', '2024-10-20', 22, 3.00, 'approved'),
(5, 1, 2, '15%', '14%', 6.67, 'achieved', 4, 'Recruitment drive successful', '2024-10-10', 8, 4, 'Good improvement', '2024-10-15', 3, 4, 'Well executed', '2024-10-20', 23, 4.00, 'approved'),
(10, 1, 2, '88%', '86%', -2.27, 'partially_achieved', 3, 'Slightly below target due to billing issues', '2024-10-10', 10, 3, 'Needs attention', '2024-10-15', 4, 3, 'Improvement required', '2024-10-20', 23, 3.00, 'approved'),
(17, 1, 2, '45', '52', 15.56, 'achieved', 5, 'Exceeded quarterly target significantly', '2024-10-10', 12, 5, 'Excellent performance', '2024-10-15', 5, 4, 'Very good', '2024-10-20', 24, 4.60, 'approved'),
(20, 1, 2, '95%', '93%', -2.11, 'partially_achieved', 3, 'Equipment challenges impacted compliance', '2024-10-10', 13, 3, 'Address equipment issues', '2024-10-15', 5, 3, 'Acceptable', '2024-10-20', 24, 3.00, 'approved'),
(25, 1, 2, '72%', '75%', 4.17, 'achieved', 4, 'Additional parks upgraded', '2024-10-10', 14, 4, 'Good work', '2024-10-15', 6, 4, 'Exceeded expectations', '2024-10-20', 22, 4.00, 'approved'),
(30, 1, 2, '75', '82', 9.33, 'achieved', 4, 'Strong SMME support program', '2024-10-10', 15, 4, 'Excellent outreach', '2024-10-15', 7, 4, 'Good progress', '2024-10-20', 23, 4.00, 'approved'),

-- Q2 Results (Mixed statuses for demonstration)
(1, 2, 2, '87%', '88%', 1.15, 'achieved', 4, 'Consistent improvement', '2025-01-10', 2, 4, 'Maintained momentum', '2025-01-15', 3, NULL, NULL, NULL, NULL, NULL, 'manager_review'),
(5, 2, 2, '13%', '12%', 7.69, 'achieved', 5, 'Below target vacancy rate - excellent', '2025-01-10', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted'),
(10, 2, 2, '89%', '90%', 1.12, 'achieved', 4, 'Recovery from Q1 shortfall', '2025-01-10', 10, 4, 'Good recovery', '2025-01-15', 4, 4, 'Improvement noted', '2025-01-18', 23, 4.00, 'approved'),
(17, 2, 2, '90', '88', -2.22, 'partially_achieved', 3, 'Rain delays affected progress', '2025-01-10', 12, 3, 'Weather impact noted', '2025-01-15', 5, NULL, NULL, NULL, NULL, NULL, 'independent_review'),
(30, 2, 2, '150', '165', 10.00, 'achieved', 5, 'Strong demand for SMME support', '2025-01-08', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted');

-- =====================================================
-- BUDGET PROJECTIONS (Monthly for FY 2024/2025)
-- =====================================================
INSERT INTO budget_projections (financial_year_id, directorate_id, month, revenue_source, projected_revenue, actual_revenue, operating_expenditure_projected, operating_expenditure_actual, capital_expenditure_projected, capital_expenditure_actual) VALUES
-- Corporate Services
(2, 2, 1, 'Grants and Subsidies', 5000000, 4850000, 6500000, 6200000, 1500000, 1200000),
(2, 2, 2, 'Grants and Subsidies', 5000000, 5100000, 6500000, 6400000, 1500000, 1400000),
(2, 2, 3, 'Grants and Subsidies', 5000000, 4950000, 6500000, 6550000, 1500000, 1300000),
(2, 2, 4, 'Grants and Subsidies', 5000000, 5200000, 6500000, 6300000, 1500000, 1600000),
(2, 2, 5, 'Grants and Subsidies', 5000000, 4800000, 6500000, 6450000, 1500000, 1450000),
(2, 2, 6, 'Grants and Subsidies', 5000000, 5050000, 6500000, 6380000, 1500000, 1480000),

-- Financial Services
(2, 3, 1, 'Property Rates', 25000000, 23500000, 8000000, 7800000, 500000, 450000),
(2, 3, 2, 'Property Rates', 25000000, 24800000, 8000000, 8100000, 500000, 480000),
(2, 3, 3, 'Property Rates', 25000000, 24200000, 8000000, 7950000, 500000, 520000),
(2, 3, 4, 'Property Rates', 25000000, 26100000, 8000000, 8050000, 500000, 490000),
(2, 3, 5, 'Property Rates', 25000000, 24500000, 8000000, 7900000, 500000, 510000),
(2, 3, 6, 'Property Rates', 25000000, 25200000, 8000000, 8200000, 500000, 470000),

-- Infrastructure - Water Revenue
(2, 4, 1, 'Water Services', 18000000, 17200000, 12000000, 11500000, 8000000, 6500000),
(2, 4, 2, 'Water Services', 18000000, 17800000, 12000000, 11800000, 8000000, 7200000),
(2, 4, 3, 'Water Services', 18000000, 17500000, 12000000, 12200000, 8000000, 7800000),
(2, 4, 4, 'Water Services', 18000000, 18200000, 12000000, 11900000, 8000000, 8100000),
(2, 4, 5, 'Water Services', 18000000, 17900000, 12000000, 12100000, 8000000, 7500000),
(2, 4, 6, 'Water Services', 18000000, 18100000, 12000000, 11700000, 8000000, 7900000),

-- Infrastructure - Electricity
(2, 4, 1, 'Electricity Sales', 35000000, 33500000, 28000000, 27500000, 5000000, 4200000),
(2, 4, 2, 'Electricity Sales', 35000000, 34200000, 28000000, 27800000, 5000000, 4800000),
(2, 4, 3, 'Electricity Sales', 35000000, 34800000, 28000000, 28200000, 5000000, 5100000),
(2, 4, 4, 'Electricity Sales', 35000000, 35500000, 28000000, 27900000, 5000000, 4900000),
(2, 4, 5, 'Electricity Sales', 35000000, 34100000, 28000000, 28100000, 5000000, 5200000),
(2, 4, 6, 'Electricity Sales', 35000000, 34900000, 28000000, 27700000, 5000000, 4700000);

-- =====================================================
-- CAPITAL PROJECTS
-- =====================================================
INSERT INTO capital_projects (financial_year_id, directorate_id, project_name, project_code, description, ward_number, total_budget, q1_budget, q2_budget, q3_budget, q4_budget, q1_spent, q2_spent, q3_spent, q4_spent, status, completion_percentage, start_date, expected_completion, funding_source, project_manager_id) VALUES
(2, 4, 'Main Road Rehabilitation Phase 2', 'ROADS-2024-001', 'Complete rehabilitation of 15km main arterial road', 'Ward 5, 7', 45000000, 10000000, 15000000, 15000000, 5000000, 9500000, 14200000, 0, 0, 'in_progress', 52.00, '2024-07-15', '2025-05-30', 'MIG Grant', 12),
(2, 4, 'Water Treatment Plant Upgrade', 'WATER-2024-001', 'Upgrade of water treatment capacity by 20ML/day', NULL, 85000000, 20000000, 25000000, 25000000, 15000000, 18500000, 22000000, 0, 0, 'in_progress', 48.00, '2024-08-01', '2025-06-30', 'RBIG Grant', 13),
(2, 4, 'Sewer Network Extension Ward 12', 'SEWER-2024-001', 'Extension of sewer network to informal settlement', 'Ward 12', 25000000, 5000000, 8000000, 8000000, 4000000, 4800000, 7500000, 0, 0, 'in_progress', 49.00, '2024-07-01', '2025-04-30', 'Own Revenue', 13),
(2, 4, 'Street Lighting Upgrade Project', 'ELEC-2024-001', 'LED street lighting conversion in CBD', 'Ward 1, 2', 12000000, 3000000, 4000000, 3000000, 2000000, 2900000, 3800000, 0, 0, 'in_progress', 56.00, '2024-07-15', '2025-03-31', 'INEP Grant', 5),
(2, 5, 'Community Park Development', 'PARKS-2024-001', 'New park development with sports facilities', 'Ward 8', 8000000, 2000000, 2500000, 2500000, 1000000, 1950000, 2400000, 0, 0, 'in_progress', 54.00, '2024-08-01', '2025-05-31', 'Own Revenue', 14),
(2, 5, 'Library Modernization', 'LIB-2024-001', 'ICT upgrade and modernization of main library', NULL, 5000000, 1500000, 1500000, 1000000, 1000000, 1450000, 1480000, 0, 0, 'in_progress', 59.00, '2024-07-01', '2025-02-28', 'Provincial Grant', 6),
(2, 6, 'Industrial Park Infrastructure', 'LED-2024-001', 'Bulk infrastructure for new industrial park', 'Ward 15', 35000000, 8000000, 10000000, 10000000, 7000000, 7500000, 9200000, 0, 0, 'in_progress', 48.00, '2024-09-01', '2025-06-30', 'NDPG', 15),
(2, 2, 'ICT Infrastructure Upgrade', 'ICT-2024-001', 'Network and server infrastructure modernization', NULL, 15000000, 5000000, 5000000, 3000000, 2000000, 4800000, 4900000, 0, 0, 'in_progress', 65.00, '2024-07-01', '2025-03-31', 'Own Revenue', 9);

-- =====================================================
-- PERFORMANCE AGREEMENTS
-- =====================================================
INSERT INTO performance_agreements (user_id, financial_year_id, agreement_date, linked_kpis, signed_by_employee, employee_signed_date, signed_by_manager, manager_signed_date, manager_id, status) VALUES
(2, 2, '2024-07-15', '[1, 2, 3, 4]', 1, '2024-07-15', 1, '2024-07-16', 1, 'active'),
(3, 2, '2024-07-15', '[5, 6, 7, 8, 9]', 1, '2024-07-15', 1, '2024-07-16', 2, 'active'),
(4, 2, '2024-07-15', '[10, 11, 12, 13, 14, 15]', 1, '2024-07-15', 1, '2024-07-16', 2, 'active'),
(5, 2, '2024-07-15', '[17, 18, 19, 20, 21, 22, 23, 24]', 1, '2024-07-15', 1, '2024-07-16', 2, 'active'),
(6, 2, '2024-07-15', '[25, 26, 27, 28, 29]', 1, '2024-07-15', 1, '2024-07-16', 2, 'active'),
(7, 2, '2024-07-15', '[30, 31, 32, 33, 34]', 1, '2024-07-15', 1, '2024-07-16', 2, 'active');

-- =====================================================
-- SAMPLE NOTIFICATIONS
-- =====================================================
INSERT INTO notifications (user_id, type, title, message, link, is_read) VALUES
(2, 'task', 'Q2 Assessment Due', 'Please complete your Q2 self-assessment by 15 January 2025', '/assessment/self', 0),
(8, 'review', 'POE Review Required', 'New POE uploaded for KPI-CORP-001 requires your review', '/poe/review/1', 0),
(22, 'task', 'Independent Assessment Required', '5 KPIs require independent assessment for Q1', '/assessment/independent', 0),
(4, 'deadline', 'Budget Revision Deadline', 'Mid-year budget revision due by 31 January 2025', '/budget/revision', 0),
(5, 'warning', 'Capital Expenditure Alert', 'Capital budget expenditure below 50% threshold', '/reports/capital', 0);

-- =====================================================
-- END OF SEED DATA
-- =====================================================
