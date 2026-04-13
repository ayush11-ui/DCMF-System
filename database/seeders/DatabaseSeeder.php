<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CaseFile;
use App\Models\Hearing;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        $admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@dcfm.court',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $judge1 = User::factory()->create([
            'name' => 'Hon. Justice Ramesh Sharma',
            'email' => 'judge@dcfm.court',
            'password' => Hash::make('password'),
            'role' => 'judge',
            'court_id' => 'COURT-01A',
        ]);

        $judge2 = User::factory()->create([
            'name' => 'Hon. Justice Priya Nair',
            'email' => 'judge2@dcfm.court',
            'password' => Hash::make('password'),
            'role' => 'judge',
            'court_id' => 'COURT-02B',
        ]);

        $lawyer = User::factory()->create([
            'name' => 'Adv. Sunita Kapoor',
            'email' => 'lawyer@dcfm.court',
            'password' => Hash::make('password'),
            'role' => 'lawyer',
            'bar_number' => 'BAR-2023-991',
        ]);

        $clerk = User::factory()->create([
            'name' => 'Rakesh Verma',
            'email' => 'clerk@dcfm.court',
            'password' => Hash::make('password'),
            'role' => 'clerk',
            'court_id' => 'REGISTRY-D1',
        ]);

        // Cases
        $casesData = [
            ['title' => 'TechCorp vs DataPrivacy Comm.', 'type' => 'Commercial', 'complexity' => 'Complex', 'priority' => 'High', 'interval' => 34, 'est' => 8, 'status' => 'Pending', 'judge' => $judge1->id, 'petitioner' => 'TechCorp Ltd', 'respondent' => 'Data Privacy Commission'],
            ['title' => 'Sharma Estate Partition', 'type' => 'Civil', 'complexity' => 'Standard', 'priority' => 'Medium', 'interval' => 30, 'est' => 5, 'status' => 'Ongoing', 'judge' => $judge1->id, 'petitioner' => 'Amit Sharma', 'respondent' => 'Rahul Sharma'],
            ['title' => 'State vs Vikram Singh', 'type' => 'Criminal', 'complexity' => 'Standard', 'priority' => 'Urgent', 'interval' => 15, 'est' => 5, 'status' => 'Ongoing', 'judge' => $judge2->id, 'petitioner' => 'State', 'respondent' => 'Vikram Singh'],
            ['title' => 'Divorce Petition: Patel', 'type' => 'Family', 'complexity' => 'Simple', 'priority' => 'Medium', 'interval' => 14, 'est' => 3, 'status' => 'Pending', 'judge' => $judge2->id, 'petitioner' => 'Anita Patel', 'respondent' => 'Rajesh Patel'],
            ['title' => 'Global Logistics Breach of Contract', 'type' => 'Commercial', 'complexity' => 'Complex', 'priority' => 'Medium', 'interval' => 45, 'est' => 8, 'status' => 'Ongoing', 'judge' => $judge1->id, 'petitioner' => 'Global Logistics', 'respondent' => 'Apex Transports'],
            ['title' => 'Municipal Corporation Zoning Viol.', 'type' => 'Civil', 'complexity' => 'Simple', 'priority' => 'Low', 'interval' => 18, 'est' => 3, 'status' => 'Closed', 'judge' => $judge2->id, 'petitioner' => 'Municipal Corp.', 'respondent' => 'Green Builders Inc.'],
        ];

        foreach ($casesData as $idx => $c) {
            $case = CaseFile::create([
                'case_number' => 'DCFM-2025-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT),
                'title' => $c['title'],
                'description' => 'Detailed description for ' . $c['title'],
                'case_type' => $c['type'],
                'complexity_level' => $c['complexity'],
                'priority' => $c['priority'],
                'status' => $c['status'],
                'assigned_judge_id' => $c['judge'],
                'assigned_lawyer_id' => $idx % 2 == 0 ? $lawyer->id : null,
                'created_by' => $clerk->id,
                'filing_date' => now()->subDays(rand(10, 100)),
                'hearing_interval_days' => $c['interval'],
                'estimated_hearings' => $c['est'],
                'hearings_held' => $c['status'] == 'Closed' ? $c['est'] : rand(0, 2),
                'petitioner' => $c['petitioner'],
                'respondent' => $c['respondent'],
                'court_name' => 'High Court ' . ($idx % 2 + 1),
            ]);

            // Add a hearing
            if ($case->status != 'Closed') {
                $hearingDate = now()->addDays(rand(1, 20));
                Hearing::create([
                    'case_id' => $case->id,
                    'judge_id' => $case->assigned_judge_id,
                    'hearing_date' => $hearingDate->format('Y-m-d'),
                    'hearing_time' => '10:30:00',
                    'duration_minutes' => 45,
                    'status' => 'Scheduled',
                    'hearing_type' => 'Standard',
                    'scheduled_by' => $clerk->id,
                    'courtroom' => 'Courtroom ' . rand(1, 5) . 'A'
                ]);
                $case->update(['next_hearing_date' => $hearingDate->setTime(10, 30)]);
            } else {
                 $case->update(['closed_date' => now()->subDays(2)]);
            }
        }
    }
}
