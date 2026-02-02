<?php
/**
 * Training Controller
 * User training materials, guides, and FAQ
 */

class TrainingController {

    public function index(): void {
        $modules = $this->getTrainingModules();

        $data = [
            'title' => 'Training Center',
            'modules' => $modules
        ];

        view('training.index', $data);
    }

    public function module(string $slug): void {
        $modules = $this->getTrainingModules();
        $module = null;

        foreach ($modules as $m) {
            if ($m['slug'] === $slug) {
                $module = $m;
                break;
            }
        }

        if (!$module) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $data = [
            'title' => $module['title'],
            'module' => $module
        ];

        view('training.module', $data);
    }

    public function faq(): void {
        $faqs = $this->getFAQs();
        $categories = array_unique(array_column($faqs, 'category'));

        $data = [
            'title' => 'Frequently Asked Questions',
            'faqs' => $faqs,
            'categories' => $categories
        ];

        view('training.faq', $data);
    }

    public function videos(): void {
        $videos = $this->getTrainingVideos();

        $data = [
            'title' => 'Training Videos',
            'videos' => $videos
        ];

        view('training.videos', $data);
    }

    public function glossary(): void {
        $terms = $this->getGlossaryTerms();

        $data = [
            'title' => 'Glossary of Terms',
            'terms' => $terms
        ];

        view('training.glossary', $data);
    }

    public function quickStart(): void {
        $data = [
            'title' => 'Quick Start Guide'
        ];

        view('training.quick-start', $data);
    }

    public function architecture(): void {
        $data = [
            'title' => 'Cloud Architecture Review'
        ];

        view('training.architecture', $data);
    }

    public function downloadArchitecture(): void {
        $file = PUBLIC_PATH . '/docs/Cloud_Architecture_Review.html';

        if (!file_exists($file)) {
            http_response_code(404);
            echo "Document not found.";
            return;
        }

        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="Cloud_Architecture_Review.html"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    private function getTrainingModules(): array {
        return [
            [
                'slug' => 'getting-started',
                'title' => 'Getting Started',
                'description' => 'Introduction to the SDBIP/IDP Management System',
                'icon' => 'rocket-takeoff',
                'duration' => '15 min',
                'level' => 'Beginner',
                'topics' => [
                    'System Overview',
                    'Logging In',
                    'Navigating the Dashboard',
                    'Understanding Your Role',
                    'Profile Settings'
                ],
                'content' => '
                    <h4>Welcome to the SDBIP/IDP Management System</h4>
                    <p>This system helps South African municipalities manage their Service Delivery and Budget Implementation Plans (SDBIP) and Integrated Development Plans (IDP) in compliance with MFMA regulations.</p>

                    <h5>Key Features</h5>
                    <ul>
                        <li><strong>Strategic Objectives:</strong> Define and track IDP strategic objectives aligned with National Development Plan priorities</li>
                        <li><strong>KPI Management:</strong> Create and monitor Key Performance Indicators with quarterly targets</li>
                        <li><strong>Multi-level Assessment:</strong> Self, Manager, and Independent assessor ratings</li>
                        <li><strong>Proof of Evidence:</strong> Upload and manage supporting documents</li>
                        <li><strong>Budget Tracking:</strong> Monitor projections and capital projects</li>
                    </ul>

                    <h5>Logging In</h5>
                    <ol>
                        <li>Navigate to the login page</li>
                        <li>Enter your username and password</li>
                        <li>Click "Sign In"</li>
                        <li>If using LDAP/Active Directory, use your network credentials</li>
                    </ol>

                    <h5>Understanding Roles</h5>
                    <table class="table table-bordered">
                        <tr><th>Role</th><th>Responsibilities</th></tr>
                        <tr><td>Employee</td><td>Self-assessment, POE uploads, view own KPIs</td></tr>
                        <tr><td>Manager</td><td>Review team assessments, approve/reject POE</td></tr>
                        <tr><td>Director</td><td>Directorate oversight, KPI approval</td></tr>
                        <tr><td>Independent Assessor</td><td>Independent ratings and verification</td></tr>
                        <tr><td>Admin</td><td>Full system access, user management</td></tr>
                    </table>
                '
            ],
            [
                'slug' => 'idp-management',
                'title' => 'IDP Management',
                'description' => 'Managing Strategic Objectives and IDP alignment',
                'icon' => 'bullseye',
                'duration' => '20 min',
                'level' => 'Intermediate',
                'topics' => [
                    'Creating Strategic Objectives',
                    'NDP Priority Alignment',
                    'Objective Weighting',
                    'Linking to Directorates',
                    'Five-Year Planning'
                ],
                'content' => '
                    <h4>Integrated Development Plan (IDP) Management</h4>
                    <p>The IDP is a 5-year strategic plan required for all municipalities in South Africa. This module guides you through managing strategic objectives.</p>

                    <h5>Creating a Strategic Objective</h5>
                    <ol>
                        <li>Navigate to IDP Management</li>
                        <li>Click "New Objective"</li>
                        <li>Enter the objective code (e.g., SO-001)</li>
                        <li>Provide a clear objective name and description</li>
                        <li>Select the aligned National Priority</li>
                        <li>Assign to a Directorate</li>
                        <li>Set the weight (importance percentage)</li>
                    </ol>

                    <h5>National Development Plan Priorities</h5>
                    <ul>
                        <li>Priority 1: Capable, ethical and developmental state</li>
                        <li>Priority 2: Economic transformation and job creation</li>
                        <li>Priority 3: Education, skills and health</li>
                        <li>Priority 4: Consolidating the social wage</li>
                        <li>Priority 5: Spatial integration and human settlements</li>
                        <li>Priority 6: Social cohesion and safe communities</li>
                        <li>Priority 7: A better Africa and world</li>
                    </ul>

                    <div class="alert alert-info">
                        <strong>Best Practice:</strong> Ensure all strategic objectives align with at least one NDP priority and support the municipality\'s vision.
                    </div>
                '
            ],
            [
                'slug' => 'sdbip-kpis',
                'title' => 'SDBIP & KPIs',
                'description' => 'Creating and managing Key Performance Indicators',
                'icon' => 'graph-up-arrow',
                'duration' => '30 min',
                'level' => 'Intermediate',
                'topics' => [
                    'Understanding SDBIP Structure',
                    'Creating SMART KPIs',
                    'Setting Quarterly Targets',
                    'SLA Categories',
                    'Budget Allocation per KPI'
                ],
                'content' => '
                    <h4>Service Delivery and Budget Implementation Plan</h4>
                    <p>The SDBIP is the operational plan that converts the IDP and budget into measurable outcomes.</p>

                    <h5>SDBIP Layers</h5>
                    <ul>
                        <li><strong>Top Layer:</strong> Council-approved, contains high-level targets</li>
                        <li><strong>Component/Directorate:</strong> Detailed operational targets</li>
                        <li><strong>Individual:</strong> Linked to performance agreements</li>
                    </ul>

                    <h5>Creating SMART KPIs</h5>
                    <p>All KPIs must be:</p>
                    <ul>
                        <li><strong>S</strong>pecific - Clear and unambiguous</li>
                        <li><strong>M</strong>easurable - Quantifiable with a unit of measure</li>
                        <li><strong>A</strong>chievable - Realistic given resources</li>
                        <li><strong>R</strong>elevant - Aligned to strategic objectives</li>
                        <li><strong>T</strong>ime-bound - With quarterly deadlines</li>
                    </ul>

                    <h5>SLA Categories</h5>
                    <table class="table table-bordered">
                        <tr><th>Category</th><th>Description</th></tr>
                        <tr><td>Budget</td><td>Financial performance indicators</td></tr>
                        <tr><td>Internal Control</td><td>Governance and compliance indicators</td></tr>
                        <tr><td>HR/Vacancy</td><td>Staff-related indicators</td></tr>
                    </table>

                    <h5>Setting Quarterly Targets</h5>
                    <p>For the SA Municipal Financial Year (July-June):</p>
                    <ul>
                        <li>Q1: July - September</li>
                        <li>Q2: October - December</li>
                        <li>Q3: January - March</li>
                        <li>Q4: April - June</li>
                    </ul>
                '
            ],
            [
                'slug' => 'quarterly-assessment',
                'title' => 'Quarterly Assessment',
                'description' => 'The assessment workflow and rating system',
                'icon' => 'clipboard-check',
                'duration' => '25 min',
                'level' => 'Intermediate',
                'topics' => [
                    'Assessment Workflow',
                    'Self Assessment',
                    'Manager Review',
                    'Independent Assessment',
                    'Rating Scale (1-5)',
                    'Aggregated Scores'
                ],
                'content' => '
                    <h4>Quarterly Assessment Process</h4>
                    <p>Performance is assessed quarterly through a multi-stakeholder process.</p>

                    <h5>Assessment Workflow</h5>
                    <div class="alert alert-secondary">
                        Employee Self-Assessment → Manager Review → Independent Assessment → Final Aggregation
                    </div>

                    <h5>Rating Scale</h5>
                    <table class="table table-bordered">
                        <tr><th>Rating</th><th>Description</th><th>Meaning</th></tr>
                        <tr><td class="text-danger">1</td><td>Unacceptable</td><td>Target significantly missed</td></tr>
                        <tr><td class="text-warning">2</td><td>Needs Improvement</td><td>Target partially missed</td></tr>
                        <tr><td class="text-info">3</td><td>Meets Expectations</td><td>Target achieved</td></tr>
                        <tr><td class="text-primary">4</td><td>Exceeds Expectations</td><td>Target exceeded</td></tr>
                        <tr><td class="text-success">5</td><td>Outstanding</td><td>Target significantly exceeded</td></tr>
                    </table>

                    <h5>Aggregated Rating Formula</h5>
                    <div class="alert alert-info">
                        <code>Final Rating = (Self × 20%) + (Manager × 40%) + (Independent × 40%)</code>
                    </div>

                    <h5>Steps for Self-Assessment</h5>
                    <ol>
                        <li>Go to Assessment → Self Assessment</li>
                        <li>Select the current quarter</li>
                        <li>For each KPI, enter your actual achievement</li>
                        <li>Provide a self-rating (1-5) with justification</li>
                        <li>Upload Proof of Evidence</li>
                        <li>Submit for Manager Review</li>
                    </ol>
                '
            ],
            [
                'slug' => 'poe-management',
                'title' => 'Proof of Evidence',
                'description' => 'Uploading and managing supporting documents',
                'icon' => 'file-earmark-check',
                'duration' => '15 min',
                'level' => 'Beginner',
                'topics' => [
                    'Acceptable File Types',
                    'Uploading Documents',
                    'POE Review Process',
                    'Resubmission Workflow',
                    'Best Practices'
                ],
                'content' => '
                    <h4>Proof of Evidence (POE) Management</h4>
                    <p>POE documents support your KPI achievements and are reviewed by managers and independent assessors.</p>

                    <h5>Acceptable File Types</h5>
                    <ul>
                        <li>PDF documents (.pdf)</li>
                        <li>Microsoft Word (.doc, .docx)</li>
                        <li>Microsoft Excel (.xls, .xlsx)</li>
                        <li>Images (.jpg, .jpeg, .png, .gif)</li>
                    </ul>

                    <h5>Maximum File Size</h5>
                    <p>10 MB per file</p>

                    <h5>Uploading POE</h5>
                    <ol>
                        <li>Navigate to the KPI or Assessment page</li>
                        <li>Click "Upload POE" or the upload icon</li>
                        <li>Select your file</li>
                        <li>Add a description (optional but recommended)</li>
                        <li>Click Upload</li>
                    </ol>

                    <h5>POE Review Status</h5>
                    <ul>
                        <li><span class="badge bg-warning">Pending</span> - Awaiting review</li>
                        <li><span class="badge bg-success">Accepted</span> - Approved by reviewer</li>
                        <li><span class="badge bg-danger">Rejected</span> - Needs resubmission</li>
                    </ul>

                    <h5>Best Practices</h5>
                    <ul>
                        <li>Use clear, descriptive file names</li>
                        <li>Ensure documents are legible</li>
                        <li>Highlight relevant sections</li>
                        <li>Include dates on all evidence</li>
                        <li>Keep original documents for audits</li>
                    </ul>
                '
            ],
            [
                'slug' => 'budget-management',
                'title' => 'Budget Management',
                'description' => 'Budget projections and capital project tracking',
                'icon' => 'cash-stack',
                'duration' => '20 min',
                'level' => 'Advanced',
                'topics' => [
                    'Monthly Projections',
                    'Revenue Tracking',
                    'Expenditure Monitoring',
                    'Capital Projects',
                    'Variance Analysis'
                ],
                'content' => '
                    <h4>Budget Management Module</h4>
                    <p>Track budget projections, actual expenditure, and capital projects in line with MFMA requirements.</p>

                    <h5>Budget Projections</h5>
                    <p>Monthly projections include:</p>
                    <ul>
                        <li>Projected vs Actual Revenue</li>
                        <li>Operating Expenditure (OPEX)</li>
                        <li>Capital Expenditure (CAPEX)</li>
                    </ul>

                    <h5>Capital Projects</h5>
                    <p>Track infrastructure and development projects with:</p>
                    <ul>
                        <li>Project code and name</li>
                        <li>Total and quarterly budgets</li>
                        <li>Quarterly expenditure</li>
                        <li>Completion percentage</li>
                        <li>Project status</li>
                    </ul>

                    <h5>Project Statuses</h5>
                    <ul>
                        <li><span class="badge bg-secondary">Planning</span></li>
                        <li><span class="badge bg-info">Procurement</span></li>
                        <li><span class="badge bg-primary">In Progress</span></li>
                        <li><span class="badge bg-success">Completed</span></li>
                        <li><span class="badge bg-warning">On Hold</span></li>
                        <li><span class="badge bg-danger">Cancelled</span></li>
                    </ul>

                    <h5>Variance Analysis</h5>
                    <p>The system automatically calculates:</p>
                    <ul>
                        <li>Budget vs Actual variance</li>
                        <li>Percentage over/under budget</li>
                        <li>Quarterly comparisons</li>
                    </ul>
                '
            ],
            [
                'slug' => 'imbizo',
                'title' => 'Mayoral Imbizo',
                'description' => 'Community engagement and action item tracking',
                'icon' => 'people',
                'duration' => '20 min',
                'level' => 'Intermediate',
                'topics' => [
                    'Creating Imbizo Sessions',
                    'Livestreaming Setup',
                    'Capturing Action Items',
                    'Assigning Responsibilities',
                    'Progress Tracking'
                ],
                'content' => '
                    <h4>Mayoral IDP Imbizo Module</h4>
                    <p>Manage community engagement sessions, capture commitments, and track action items.</p>

                    <h5>What is an Imbizo?</h5>
                    <p>An Imbizo is a traditional community gathering where the Mayor engages directly with residents to hear concerns and make commitments.</p>

                    <h5>Creating an Imbizo Session</h5>
                    <ol>
                        <li>Go to Mayoral Imbizo → New Session</li>
                        <li>Enter session details (date, ward, venue)</li>
                        <li>Add livestream URLs (YouTube, Facebook, Twitter)</li>
                        <li>Save the session</li>
                    </ol>

                    <h5>Livestreaming</h5>
                    <p>Supported platforms:</p>
                    <ul>
                        <li>YouTube Live</li>
                        <li>Facebook Live</li>
                        <li>X (Twitter) Live</li>
                    </ul>

                    <h5>Action Items</h5>
                    <p>Commitments made during the Imbizo are captured as action items with:</p>
                    <ul>
                        <li>Description of the commitment</li>
                        <li>Assigned directorate/department</li>
                        <li>Responsible person</li>
                        <li>Due date</li>
                        <li>Priority level</li>
                        <li>Status tracking</li>
                    </ul>

                    <h5>AI Minutes Generation</h5>
                    <p>If OpenAI is configured, the system can automatically generate meeting minutes from recorded action items and comments.</p>
                '
            ]
        ];
    }

    private function getFAQs(): array {
        return [
            // General
            [
                'category' => 'General',
                'question' => 'What is the SDBIP/IDP Management System?',
                'answer' => 'The SDBIP/IDP Management System is a comprehensive tool for South African municipalities to manage their Service Delivery and Budget Implementation Plans (SDBIP) and Integrated Development Plans (IDP). It helps track strategic objectives, KPIs, budgets, and performance assessments in compliance with MFMA regulations.'
            ],
            [
                'category' => 'General',
                'question' => 'What is the South African Municipal Financial Year?',
                'answer' => 'The SA Municipal Financial Year runs from 1 July to 30 June. Quarters are: Q1 (Jul-Sep), Q2 (Oct-Dec), Q3 (Jan-Mar), Q4 (Apr-Jun).'
            ],
            [
                'category' => 'General',
                'question' => 'How do I reset my password?',
                'answer' => 'Contact your system administrator to reset your password. If LDAP is enabled, use your network/Active Directory password.'
            ],
            [
                'category' => 'General',
                'question' => 'What browsers are supported?',
                'answer' => 'The system works best with modern browsers: Google Chrome, Mozilla Firefox, Microsoft Edge, and Safari. Internet Explorer is not supported.'
            ],

            // KPIs
            [
                'category' => 'KPIs',
                'question' => 'What makes a good KPI?',
                'answer' => 'A good KPI is SMART: Specific (clear and focused), Measurable (quantifiable), Achievable (realistic), Relevant (aligned to objectives), and Time-bound (with deadlines). It should have a clear unit of measure and baseline.'
            ],
            [
                'category' => 'KPIs',
                'question' => 'How are quarterly targets calculated?',
                'answer' => 'Quarterly targets should be set based on the annual target, considering seasonal variations and resource availability. The sum of quarterly targets typically equals or builds toward the annual target.'
            ],
            [
                'category' => 'KPIs',
                'question' => 'What are SLA categories?',
                'answer' => 'SLA (Service Level Agreement) categories classify KPIs: Budget (financial indicators), Internal Control (governance and compliance), and HR/Vacancy (staff-related indicators). These categories help with reporting and analysis.'
            ],
            [
                'category' => 'KPIs',
                'question' => 'Can I modify a KPI after creation?',
                'answer' => 'Yes, KPIs can be edited by users with Manager or Admin roles. However, changes should be documented and approved, especially after the SDBIP has been adopted by Council.'
            ],

            // Assessment
            [
                'category' => 'Assessment',
                'question' => 'How does the rating system work?',
                'answer' => 'Ratings are on a 1-5 scale: 1 (Unacceptable), 2 (Needs Improvement), 3 (Meets Expectations), 4 (Exceeds Expectations), 5 (Outstanding). The final aggregated rating combines Self (20%), Manager (40%), and Independent (40%) ratings.'
            ],
            [
                'category' => 'Assessment',
                'question' => 'What happens if my POE is rejected?',
                'answer' => 'If your POE is rejected, you will receive feedback explaining why. You must upload revised evidence within the resubmission deadline (typically 7 days). The assessment remains pending until acceptable POE is provided.'
            ],
            [
                'category' => 'Assessment',
                'question' => 'Can I dispute a rating?',
                'answer' => 'Yes, you can add comments to explain your position. The final aggregated rating considers all inputs. Formal disputes should be raised through your HR department following the municipality\'s performance management policy.'
            ],
            [
                'category' => 'Assessment',
                'question' => 'When are quarterly assessments due?',
                'answer' => 'Assessments are typically due within 30 days after each quarter ends. Check with your manager for specific deadlines. The system shows deadline notifications on your dashboard.'
            ],

            // POE
            [
                'category' => 'Proof of Evidence',
                'question' => 'What file types can I upload?',
                'answer' => 'Accepted file types: PDF (.pdf), Word documents (.doc, .docx), Excel spreadsheets (.xls, .xlsx), and images (.jpg, .jpeg, .png, .gif). Maximum file size is 10MB per file.'
            ],
            [
                'category' => 'Proof of Evidence',
                'question' => 'What makes good evidence?',
                'answer' => 'Good evidence is: dated, clearly linked to the KPI, authentic (original or certified copies), legible, and highlights the relevant achievement. Include context where necessary.'
            ],
            [
                'category' => 'Proof of Evidence',
                'question' => 'Can I upload multiple files for one KPI?',
                'answer' => 'Yes, you can upload multiple POE documents for each KPI. Each file should be clearly named to indicate its purpose.'
            ],

            // Budget
            [
                'category' => 'Budget',
                'question' => 'How often should budget projections be updated?',
                'answer' => 'Budget projections should be reviewed and updated monthly. Actual figures should be captured as soon as they are available from the financial system.'
            ],
            [
                'category' => 'Budget',
                'question' => 'What is variance analysis?',
                'answer' => 'Variance analysis compares projected (budgeted) amounts to actual amounts. Positive variance means under-budget (for expenses) or over-target (for revenue). Negative variance indicates overspending or underperformance.'
            ],

            // Reports
            [
                'category' => 'Reports',
                'question' => 'Can I export reports to Excel?',
                'answer' => 'Yes, most reports have an "Export Excel" or "Export CSV" button that downloads the data in spreadsheet format for further analysis.'
            ],
            [
                'category' => 'Reports',
                'question' => 'What is the AI Report feature?',
                'answer' => 'When configured with an OpenAI API key, the system can generate narrative performance reports using AI. These provide insights, trend analysis, and recommendations based on your data.'
            ],

            // Technical
            [
                'category' => 'Technical',
                'question' => 'Why am I getting logged out frequently?',
                'answer' => 'Sessions expire after a period of inactivity (default 1 hour). Save your work regularly. If the problem persists, contact your IT administrator.'
            ],
            [
                'category' => 'Technical',
                'question' => 'The page is loading slowly. What should I do?',
                'answer' => 'Try refreshing the page, clearing your browser cache, or using a different browser. If the issue persists, report it to your IT administrator.'
            ],
            [
                'category' => 'Technical',
                'question' => 'How do I report a bug or request a feature?',
                'answer' => 'Contact your system administrator with a detailed description of the issue or request. Include screenshots if possible.'
            ]
        ];
    }

    private function getTrainingVideos(): array {
        return [
            [
                'title' => 'System Overview',
                'description' => 'A complete walkthrough of the SDBIP/IDP Management System',
                'duration' => '10:30',
                'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
                'url' => '#',
                'category' => 'Getting Started'
            ],
            [
                'title' => 'Creating Your First KPI',
                'description' => 'Step-by-step guide to creating SMART KPIs',
                'duration' => '8:45',
                'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
                'url' => '#',
                'category' => 'KPIs'
            ],
            [
                'title' => 'Quarterly Assessment Process',
                'description' => 'How to complete self-assessments and reviews',
                'duration' => '12:20',
                'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
                'url' => '#',
                'category' => 'Assessment'
            ],
            [
                'title' => 'Uploading Proof of Evidence',
                'description' => 'Best practices for POE documentation',
                'duration' => '6:15',
                'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
                'url' => '#',
                'category' => 'POE'
            ]
        ];
    }

    private function getGlossaryTerms(): array {
        return [
            ['term' => 'Aggregated Rating', 'definition' => 'The final performance score calculated by combining Self (20%), Manager (40%), and Independent (40%) ratings.'],
            ['term' => 'Baseline', 'definition' => 'The starting point or reference value against which progress is measured.'],
            ['term' => 'CAPEX', 'definition' => 'Capital Expenditure - spending on long-term assets like infrastructure and equipment.'],
            ['term' => 'Directorate', 'definition' => 'A major functional division of the municipality headed by a Director.'],
            ['term' => 'Financial Year', 'definition' => 'The 12-month period for budgeting and reporting. SA municipalities use 1 July - 30 June.'],
            ['term' => 'IDP', 'definition' => 'Integrated Development Plan - a 5-year strategic plan required for all SA municipalities.'],
            ['term' => 'Imbizo', 'definition' => 'A traditional community gathering where leaders engage directly with residents.'],
            ['term' => 'KPI', 'definition' => 'Key Performance Indicator - a measurable value demonstrating progress toward objectives.'],
            ['term' => 'MFMA', 'definition' => 'Municipal Finance Management Act - legislation governing municipal financial management.'],
            ['term' => 'NDP', 'definition' => 'National Development Plan - South Africa\'s long-term development vision.'],
            ['term' => 'OPEX', 'definition' => 'Operating Expenditure - day-to-day running costs like salaries and utilities.'],
            ['term' => 'POE', 'definition' => 'Proof of Evidence - documents supporting claimed KPI achievements.'],
            ['term' => 'Quarter', 'definition' => 'A 3-month period. Q1: Jul-Sep, Q2: Oct-Dec, Q3: Jan-Mar, Q4: Apr-Jun.'],
            ['term' => 'SDBIP', 'definition' => 'Service Delivery and Budget Implementation Plan - the annual operational plan.'],
            ['term' => 'SLA', 'definition' => 'Service Level Agreement - defines expected service standards and categories.'],
            ['term' => 'SMART', 'definition' => 'Criteria for good objectives: Specific, Measurable, Achievable, Relevant, Time-bound.'],
            ['term' => 'Strategic Objective', 'definition' => 'A high-level goal aligned with the IDP and NDP priorities.'],
            ['term' => 'Variance', 'definition' => 'The difference between planned/budgeted values and actual results.'],
            ['term' => 'Ward', 'definition' => 'A geographic electoral subdivision of a municipality.']
        ];
    }
}
