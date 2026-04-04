<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TaskHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Employees ──
        $employees = [];
        $employeeData = [
            ['name' => 'Amit Sharma',     'email' => 'amit.sharma@company.com',     'phone' => '9876543210', 'role' => 'Senior Developer',   'address' => '12, MG Road, Bangalore'],
            ['name' => 'Priya Patel',     'email' => 'priya.patel@company.com',     'phone' => '9876543211', 'role' => 'UI/UX Designer',     'address' => '45, Park Street, Mumbai'],
            ['name' => 'Rahul Verma',     'email' => 'rahul.verma@company.com',     'phone' => '9876543212', 'role' => 'Backend Developer',  'address' => '78, Nehru Place, Delhi'],
            ['name' => 'Sneha Gupta',     'email' => 'sneha.gupta@company.com',     'phone' => '9876543213', 'role' => 'QA Engineer',        'address' => '23, Anna Nagar, Chennai'],
            ['name' => 'Vikram Singh',    'email' => 'vikram.singh@company.com',    'phone' => '9876543214', 'role' => 'DevOps Engineer',    'address' => '56, Banjara Hills, Hyderabad'],
            ['name' => 'Neha Reddy',      'email' => 'neha.reddy@company.com',      'phone' => '9876543215', 'role' => 'Frontend Developer', 'address' => '89, Koramangala, Bangalore'],
            ['name' => 'Arjun Mehta',     'email' => 'arjun.mehta@company.com',     'phone' => '9876543216', 'role' => 'Full Stack Developer', 'address' => '34, Andheri West, Mumbai'],
            ['name' => 'Kavita Joshi',    'email' => 'kavita.joshi@company.com',    'phone' => '9876543217', 'role' => 'Project Manager',    'address' => '67, Sector 18, Noida'],
        ];

        foreach ($employeeData as $data) {
            $employees[] = User::create(array_merge($data, [
                'password' => bcrypt('employee123'),
                'is_admin' => false,
            ]));
        }

        // ── Clients ──
        $clients = [];
        $clientData = [
            ['name' => 'TechVision Solutions',  'email' => 'contact@techvision.com',    'phone' => '011-25436789', 'company' => 'TechVision Solutions Pvt Ltd',  'address' => 'Tower B, Cyber City, Gurugram'],
            ['name' => 'GreenLeaf Organics',    'email' => 'info@greenleaf.com',        'phone' => '080-43567890', 'company' => 'GreenLeaf Organics Ltd',        'address' => 'HSR Layout, Bangalore'],
            ['name' => 'CloudNine Healthcare',  'email' => 'admin@cloudnine.in',        'phone' => '044-56789012', 'company' => 'CloudNine Healthcare Pvt Ltd',  'address' => 'Adyar, Chennai'],
            ['name' => 'UrbanNest Realty',      'email' => 'sales@urbannest.com',       'phone' => '022-67890123', 'company' => 'UrbanNest Realty Group',        'address' => 'Powai, Mumbai'],
            ['name' => 'EduSpark Academy',      'email' => 'hello@eduspark.com',        'phone' => '040-78901234', 'company' => 'EduSpark Academy Pvt Ltd',      'address' => 'Jubilee Hills, Hyderabad'],
        ];

        foreach ($clientData as $data) {
            $clients[] = Client::create($data);
        }

        // ── Projects ──
        $projects = [];
        $projectData = [
            ['name' => 'E-Commerce Platform Revamp',   'description' => 'Complete redesign and rebuild of the e-commerce platform with modern UI, improved checkout flow, and real-time inventory management.',                 'status' => 'in_progress', 'client_id' => $clients[0]->id, 'start_date' => '2026-01-15', 'end_date' => '2026-06-30'],
            ['name' => 'Organic Food Delivery App',    'description' => 'Mobile-first web application for organic food delivery with route optimization, live tracking, and subscription management.',                          'status' => 'in_progress', 'client_id' => $clients[1]->id, 'start_date' => '2026-02-01', 'end_date' => '2026-07-31'],
            ['name' => 'Patient Management System',    'description' => 'Comprehensive hospital management system covering patient records, appointment scheduling, billing, and pharmacy integration.',                        'status' => 'not_started', 'client_id' => $clients[2]->id, 'start_date' => '2026-04-15', 'end_date' => '2026-12-31'],
            ['name' => 'Property Listing Portal',      'description' => 'Real estate listing portal with virtual tours, neighborhood analytics, EMI calculator, and agent management.',                                          'status' => 'completed',   'client_id' => $clients[3]->id, 'start_date' => '2025-09-01', 'end_date' => '2026-02-28'],
            ['name' => 'Online Learning Platform',     'description' => 'Interactive learning management system with video courses, live classes, quizzes, certificates, and progress tracking.',                                'status' => 'in_progress', 'client_id' => $clients[4]->id, 'start_date' => '2026-03-01', 'end_date' => '2026-09-30'],
            ['name' => 'Internal HR Dashboard',        'description' => 'Internal tool for HR team to manage leave requests, payroll processing, performance reviews, and employee onboarding.',                                 'status' => 'on_hold',     'client_id' => null,            'start_date' => '2026-01-01', 'end_date' => '2026-05-31'],
        ];

        foreach ($projectData as $data) {
            $projects[] = Project::create($data);
        }

        $admin = User::where('is_admin', true)->first();

        // ── Tasks ──
        $taskData = [
            // E-Commerce Platform tasks
            ['title' => 'Design new homepage layout',               'project' => 0, 'assignee' => 1, 'status' => 'completed',   'priority' => 'high',   'type' => 'feature',     'category' => 'design',     'start' => '-30', 'due' => '-10', 'desc' => 'Create wireframes and high-fidelity mockups for the new homepage including hero banner, featured products, and promotional sections.'],
            ['title' => 'Implement product search with filters',    'project' => 0, 'assignee' => 0, 'status' => 'in_progress', 'priority' => 'urgent', 'type' => 'feature',     'category' => 'backend',    'start' => '-20', 'due' => '+5',  'desc' => 'Build Elasticsearch-based product search with faceted filters for category, price range, brand, ratings, and availability.'],
            ['title' => 'Shopping cart API development',            'project' => 0, 'assignee' => 2, 'status' => 'in_review',   'priority' => 'high',   'type' => 'feature',     'category' => 'backend',    'start' => '-15', 'due' => '+2',  'desc' => 'RESTful API for cart operations: add, remove, update quantity, apply coupons, calculate totals with tax.'],
            ['title' => 'Payment gateway integration',             'project' => 0, 'assignee' => 6, 'status' => 'todo',        'priority' => 'urgent', 'type' => 'feature',     'category' => 'backend',    'start' => '+0',  'due' => '+15', 'desc' => 'Integrate Razorpay and Stripe payment gateways with support for UPI, cards, net banking, and wallets.'],
            ['title' => 'Fix product image carousel bug',          'project' => 0, 'assignee' => 5, 'status' => 'completed',   'priority' => 'medium', 'type' => 'bug',         'category' => 'frontend',   'start' => '-25', 'due' => '-18', 'desc' => 'Image carousel on product detail page breaks on mobile when more than 5 images are loaded.'],
            ['title' => 'Implement order tracking system',          'project' => 0, 'assignee' => 0, 'status' => 'todo',        'priority' => 'medium', 'type' => 'feature',     'category' => 'backend',    'start' => '+5',  'due' => '+20', 'desc' => 'Real-time order status tracking with SMS and email notifications at each stage.'],
            ['title' => 'Performance optimization for product listing', 'project' => 0, 'assignee' => 4, 'status' => 'in_progress', 'priority' => 'high', 'type' => 'improvement', 'category' => 'devops', 'start' => '-5', 'due' => '+8', 'desc' => 'Optimize database queries, implement Redis caching, and CDN for product images to improve page load time.'],
            ['title' => 'Write unit tests for checkout flow',       'project' => 0, 'assignee' => 3, 'status' => 'in_progress', 'priority' => 'medium', 'type' => 'feature',     'category' => 'testing',    'start' => '-10', 'due' => '+3',  'desc' => 'Comprehensive test suite covering cart calculations, coupon validation, payment processing, and order creation.'],

            // Organic Food Delivery App tasks
            ['title' => 'Design delivery tracking UI',             'project' => 1, 'assignee' => 1, 'status' => 'in_progress', 'priority' => 'high',   'type' => 'feature',     'category' => 'design',     'start' => '-10', 'due' => '+5',  'desc' => 'Real-time map-based delivery tracking interface with driver location, ETA, and order status updates.'],
            ['title' => 'Build subscription management module',    'project' => 1, 'assignee' => 6, 'status' => 'todo',        'priority' => 'medium', 'type' => 'feature',     'category' => 'backend',    'start' => '+5',  'due' => '+25', 'desc' => 'Weekly/monthly subscription boxes with customizable items, pause/resume, and auto-renewal.'],
            ['title' => 'Implement route optimization algorithm',  'project' => 1, 'assignee' => 2, 'status' => 'in_progress', 'priority' => 'urgent', 'type' => 'feature',     'category' => 'backend',    'start' => '-8',  'due' => '+7',  'desc' => 'Optimize delivery routes using Google Maps API for minimum distance and time with multiple stops.'],
            ['title' => 'Setup CI/CD pipeline',                    'project' => 1, 'assignee' => 4, 'status' => 'completed',   'priority' => 'high',   'type' => 'improvement', 'category' => 'devops',     'start' => '-20', 'due' => '-12', 'desc' => 'GitHub Actions pipeline with automated testing, staging deployment, and production release workflow.'],
            ['title' => 'Food quality feedback system',            'project' => 1, 'assignee' => 5, 'status' => 'todo',        'priority' => 'low',    'type' => 'feature',     'category' => 'frontend',   'start' => '+10', 'due' => '+30', 'desc' => 'Rating and review system with photo uploads for delivered food quality feedback.'],
            ['title' => 'Fix notification delivery delays',        'project' => 1, 'assignee' => 4, 'status' => 'in_review',   'priority' => 'high',   'type' => 'bug',         'category' => 'backend',    'start' => '-5',  'due' => '+1',  'desc' => 'Push notifications arrive 5-10 minutes late. Investigate Firebase Cloud Messaging queue configuration.'],

            // Patient Management System tasks
            ['title' => 'Database schema design for patient records', 'project' => 2, 'assignee' => 2, 'status' => 'todo',     'priority' => 'urgent', 'type' => 'feature',     'category' => 'backend',    'start' => '+10', 'due' => '+25', 'desc' => 'Design normalized database schema for patient demographics, medical history, prescriptions, and lab results.'],
            ['title' => 'Appointment scheduling wireframes',       'project' => 2, 'assignee' => 1, 'status' => 'todo',        'priority' => 'high',   'type' => 'feature',     'category' => 'design',     'start' => '+10', 'due' => '+20', 'desc' => 'Calendar-based appointment booking UI with doctor availability, time slots, and conflict detection.'],

            // Property Listing Portal tasks (completed project)
            ['title' => 'Virtual tour 360° integration',           'project' => 3, 'assignee' => 6, 'status' => 'completed',   'priority' => 'high',   'type' => 'feature',     'category' => 'frontend',   'start' => '-90', 'due' => '-60', 'desc' => 'Integrate Matterport 360° virtual tours for property listings with VR headset support.'],
            ['title' => 'EMI calculator widget',                   'project' => 3, 'assignee' => 5, 'status' => 'completed',   'priority' => 'medium', 'type' => 'feature',     'category' => 'frontend',   'start' => '-80', 'due' => '-55', 'desc' => 'Interactive EMI calculator with bank rate comparison, amortization schedule, and PDF export.'],
            ['title' => 'SEO optimization for listings',           'project' => 3, 'assignee' => 0, 'status' => 'completed',   'priority' => 'medium', 'type' => 'improvement', 'category' => 'frontend',   'start' => '-70', 'due' => '-45', 'desc' => 'Implement structured data markup, dynamic sitemap, meta tags, and Open Graph for property listings.'],
            ['title' => 'Agent commission reporting',              'project' => 3, 'assignee' => 7, 'status' => 'completed',   'priority' => 'low',    'type' => 'feature',     'category' => 'backend',    'start' => '-65', 'due' => '-40', 'desc' => 'Monthly commission reports for agents with deal breakdowns, targets vs actuals, and PDF export.'],

            // Online Learning Platform tasks
            ['title' => 'Video streaming infrastructure setup',    'project' => 4, 'assignee' => 4, 'status' => 'completed',   'priority' => 'urgent', 'type' => 'feature',     'category' => 'devops',     'start' => '-25', 'due' => '-10', 'desc' => 'Setup AWS CloudFront CDN with adaptive bitrate streaming, DRM protection, and offline download support.'],
            ['title' => 'Build quiz engine with auto-grading',    'project' => 4, 'assignee' => 2, 'status' => 'in_progress', 'priority' => 'high',   'type' => 'feature',     'category' => 'backend',    'start' => '-12', 'due' => '+8',  'desc' => 'Quiz engine supporting MCQ, fill-in-blank, matching, and coding challenges with instant auto-grading.'],
            ['title' => 'Certificate generation system',           'project' => 4, 'assignee' => 6, 'status' => 'todo',        'priority' => 'medium', 'type' => 'feature',     'category' => 'backend',    'start' => '+5',  'due' => '+20', 'desc' => 'Generate PDF certificates with QR code verification, custom templates per course, and LinkedIn sharing.'],
            ['title' => 'Student progress dashboard design',      'project' => 4, 'assignee' => 1, 'status' => 'completed',   'priority' => 'high',   'type' => 'feature',     'category' => 'design',     'start' => '-20', 'due' => '-8',  'desc' => 'Dashboard showing course completion %, quiz scores, time spent, streak calendar, and skill radar chart.'],
            ['title' => 'Live class WebRTC integration',           'project' => 4, 'assignee' => 0, 'status' => 'in_progress', 'priority' => 'urgent', 'type' => 'feature',     'category' => 'frontend',   'start' => '-7',  'due' => '+10', 'desc' => 'Integrate Jitsi Meet for live classes with screen sharing, whiteboard, chat, hand raise, and recording.'],
            ['title' => 'API documentation with Swagger',          'project' => 4, 'assignee' => 7, 'status' => 'on_hold',     'priority' => 'low',    'type' => 'feature',     'category' => 'documentation', 'start' => '-5', 'due' => '+15', 'desc' => 'Auto-generate OpenAPI 3.0 docs for all REST endpoints with request/response examples.'],

            // Internal HR Dashboard tasks
            ['title' => 'Leave management module wireframes',      'project' => 5, 'assignee' => 1, 'status' => 'on_hold',     'priority' => 'medium', 'type' => 'feature',     'category' => 'design',     'start' => '-15', 'due' => '+10', 'desc' => 'UI design for leave application, approval workflow, balance tracking, and holiday calendar.'],
            ['title' => 'Payroll calculation engine',              'project' => 5, 'assignee' => 2, 'status' => 'on_hold',     'priority' => 'high',   'type' => 'feature',     'category' => 'backend',    'start' => '-10', 'due' => '+20', 'desc' => 'Automated payroll with tax deductions (IT, PF, ESI), reimbursements, bonuses, and payslip generation.'],

            // Some overdue tasks
            ['title' => 'Fix mobile responsive issues on checkout', 'project' => 0, 'assignee' => 5, 'status' => 'in_progress', 'priority' => 'urgent', 'type' => 'bug',       'category' => 'frontend',   'start' => '-15', 'due' => '-3',  'desc' => 'Checkout page layout breaks on iPhone SE and small Android devices. Address field overlaps payment section.'],
            ['title' => 'Update user documentation',               'project' => 1, 'assignee' => 7, 'status' => 'todo',        'priority' => 'low',    'type' => 'feature',     'category' => 'documentation', 'start' => '-10', 'due' => '-2', 'desc' => 'Update API docs and user guide for the latest delivery tracking features and subscription changes.'],
        ];

        $tasks = [];
        foreach ($taskData as $td) {
            $tasks[] = Task::create([
                'title'       => $td['title'],
                'description' => $td['desc'],
                'project_id'  => $projects[$td['project']]->id,
                'assigned_to' => $employees[$td['assignee']]->id,
                'created_by'  => $admin->id,
                'start_date'  => Carbon::today()->addDays((int) $td['start']),
                'due_date'    => Carbon::today()->addDays((int) $td['due']),
                'status'      => $td['status'],
                'priority'    => $td['priority'],
                'type'        => $td['type'],
                'category'    => $td['category'],
                'created_at'  => Carbon::today()->addDays((int) $td['start']),
            ]);
        }

        // ── Task Histories ──
        foreach ($tasks as $task) {
            TaskHistory::create([
                'task_id'    => $task->id,
                'user_id'    => $admin->id,
                'action'     => 'created',
                'details'    => 'Task created and assigned to ' . $task->assignee->name,
                'created_at' => $task->created_at,
            ]);

            if ($task->status !== 'todo') {
                TaskHistory::create([
                    'task_id'    => $task->id,
                    'user_id'    => $task->assigned_to,
                    'action'     => 'status_changed',
                    'details'    => "Status changed from 'todo' to '{$task->status}'",
                    'created_at' => $task->created_at->addDays(rand(1, 5)),
                ]);
            }
        }

        // ── Task Comments ──
        $commentTexts = [
            'I have started working on this. Will update once the initial version is ready.',
            'Can we schedule a quick call to discuss the requirements in detail?',
            'Pushed the first draft to the feature branch. Please review when you get a chance.',
            'Found a couple of edge cases we need to handle. Documenting them now.',
            'This is looking great! Just a few minor UI tweaks needed.',
            'Blocked on this - waiting for the API endpoint from the backend team.',
            'Completed the implementation. Moving to code review.',
            'Added unit tests covering all the major scenarios.',
            'The client approved the design mockups. We can proceed with development.',
            'Updated the PR with the requested changes. Ready for re-review.',
            'Performance benchmarks look good - 40% improvement in load time.',
            'Need clarification on the business logic for discount calculations.',
        ];

        $replyTexts = [
            'Sure, I will look into it.',
            'Thanks for the update! Keep going.',
            'I have reviewed it. Left some comments on the PR.',
            'Good catch! Let me fix that.',
            'Agreed. Let me update the implementation.',
            'Will have it done by end of day.',
        ];

        foreach ($tasks as $i => $task) {
            $numComments = rand(1, 3);
            for ($c = 0; $c < $numComments; $c++) {
                $comment = TaskComment::create([
                    'task_id'    => $task->id,
                    'user_id'    => $task->assigned_to,
                    'comment'    => $commentTexts[array_rand($commentTexts)],
                    'created_at' => $task->created_at->addDays(rand(1, 10))->addHours(rand(9, 18)),
                ]);

                if (rand(0, 1)) {
                    TaskComment::create([
                        'task_id'    => $task->id,
                        'user_id'    => $admin->id,
                        'comment'    => $replyTexts[array_rand($replyTexts)],
                        'parent_id'  => $comment->id,
                        'created_at' => $comment->created_at->addHours(rand(1, 8)),
                    ]);
                }

                if (rand(0, 2) === 0) {
                    $otherEmployee = $employees[array_rand($employees)];
                    TaskComment::create([
                        'task_id'    => $task->id,
                        'user_id'    => $otherEmployee->id,
                        'comment'    => $replyTexts[array_rand($replyTexts)],
                        'parent_id'  => $comment->id,
                        'created_at' => $comment->created_at->addHours(rand(2, 12)),
                    ]);
                }
            }
        }

        // ── Attendance Records (last 14 days for all employees) ──
        for ($dayOffset = 13; $dayOffset >= 0; $dayOffset--) {
            $date = Carbon::today()->subDays($dayOffset);
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees as $emp) {
                if (rand(1, 10) <= 8) {
                    $loginHour = rand(8, 10);
                    $loginMin  = rand(0, 59);
                    $loginAt   = $date->copy()->setTime($loginHour, $loginMin);

                    $logoutAt = null;
                    if ($dayOffset > 0) {
                        $logoutHour = rand(17, 20);
                        $logoutMin  = rand(0, 59);
                        $logoutAt   = $date->copy()->setTime($logoutHour, $logoutMin);
                    }

                    Attendance::create([
                        'user_id'   => $emp->id,
                        'login_at'  => $loginAt,
                        'logout_at' => $logoutAt,
                        'date'      => $date->toDateString(),
                    ]);
                }
            }
        }

        $this->command->info('Dummy data seeded: 8 employees, 5 clients, 6 projects, ' . count($tasks) . ' tasks, comments, histories & 14 days attendance.');
    }
}
