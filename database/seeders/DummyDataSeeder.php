<?php

namespace Database\Seeders;

use App\Models\CoachStudent;
use App\Models\Feedback;
use App\Models\Practice;
use App\Models\Skill;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users with different roles
        $this->createTestUsers();
        
        // Create teams
        $this->createTeams();
        
        // Create team invitations
        $this->createTeamInvitations();
        
        // Assign students to coaches
        $this->assignCoaches();
        
        // Create feedback
        $this->createFeedback();
        
        $this->command->info('Dummy data created successfully!');
    }
    
    /**
     * Create test users with different roles.
     */
    private function createTestUsers(): void
    {
        // Create students
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Student $i",
                'email' => "student{$i}@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => User::ROLE_STUDENT,
            ]);
            
            $user->assignRole('student');
        }
        
        // Create subject mentors
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => "Subject Mentor $i",
                'email' => "mentor{$i}@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => User::ROLE_SUBJECT_MENTOR,
            ]);
            
            $user->assignRole('subject_mentor');
        }
        
        // Create personal coaches
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Personal Coach $i",
                'email' => "coach{$i}@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => User::ROLE_PERSONAL_COACH,
            ]);
            
            $user->assignRole('personal_coach');
        }
    }
    
    /**
     * Create teams and add members.
     */
    private function createTeams(): void
    {
        // Get the admin user
        $admin = User::where('email', 'admin@example.com')->first();
        
        // Get students
        $students = User::role('student')->get();
        
        // Get mentors and coaches
        $mentors = User::role('subject_mentor')->get();
        $coaches = User::role('personal_coach')->get();
        
        // Create 3 teams
        for ($i = 1; $i <= 3; $i++) {
            $team = Team::create([
                'name' => "Team $i",
                'description' => "This is a description for Team $i",
                'created_by' => $admin->id,
            ]);
            
            // Add users to each team
            // Students - distribute evenly across teams
            $teamStudents = $students->slice(($i-1) * 3, 3);
            foreach ($teamStudents as $student) {
                $team->users()->attach($student->id, ['role' => User::ROLE_STUDENT]);
            }
            
            // Subject mentors - assign one per team
            $mentor = $mentors->get($i - 1);
            if ($mentor) {
                $team->users()->attach($mentor->id, ['role' => User::ROLE_SUBJECT_MENTOR]);
            }
            
            // Personal coaches - distribute across teams
            if ($i <= count($coaches)) {
                $coach = $coaches->get($i - 1);
                $team->users()->attach($coach->id, ['role' => User::ROLE_PERSONAL_COACH]);
            }
        }
    }
    
    /**
     * Create team invitations.
     */
    private function createTeamInvitations(): void
    {
        // Get teams
        $teams = Team::all();
        
        // Create pending invitations for each team
        foreach ($teams as $team) {
            // Get the team creator
            $creator = $team->creator;
            
            // Create 2 pending invitations per team
            for ($i = 1; $i <= 2; $i++) {
                TeamInvitation::create([
                    'team_id' => $team->id,
                    'invited_by' => $creator->id,
                    'email' => "new{$team->id}user{$i}@example.com",
                    'role' => $i % 2 === 0 ? User::ROLE_STUDENT : User::ROLE_SUBJECT_MENTOR,
                    'token' => Str::random(32),
                    'expires_at' => now()->addDays(7),
                ]);
            }
            
            // Create 1 expired invitation
            TeamInvitation::create([
                'team_id' => $team->id,
                'invited_by' => $creator->id,
                'email' => "expired{$team->id}@example.com",
                'role' => User::ROLE_STUDENT,
                'token' => Str::random(32),
                'expires_at' => now()->subDays(3),
            ]);
            
            // Create 1 accepted invitation
            TeamInvitation::create([
                'team_id' => $team->id,
                'invited_by' => $creator->id,
                'email' => "accepted{$team->id}@example.com",
                'role' => User::ROLE_STUDENT,
                'token' => Str::random(32),
                'expires_at' => now()->addDays(7),
                'accepted_at' => now()->subDays(1),
            ]);
        }
    }
    
    /**
     * Assign coaches to students.
     */
    private function assignCoaches(): void
    {
        // Get teams
        $teams = Team::all();
        
        foreach ($teams as $team) {
            // Get students and coaches for this team
            $students = $team->students;
            $coaches = $team->personalCoaches;
            
            if ($students->isNotEmpty() && $coaches->isNotEmpty()) {
                // Assign the first coach to all students in this team
                $coach = $coaches->first();
                
                foreach ($students as $student) {
                    CoachStudent::create([
                        'coach_id' => $coach->id,
                        'student_id' => $student->id,
                        'team_id' => $team->id,
                    ]);
                }
            }
        }
    }
    
    /**
     * Create feedback entries.
     */
    private function createFeedback(): void
    {
        // Get skills and practices
        $skills = Skill::with('practices')->get();
        
        // Get teams
        $teams = Team::with(['students', 'personalCoaches', 'subjectMentors'])->get();
        
        foreach ($teams as $team) {
            $students = $team->students;
            $mentors = $team->subjectMentors;
            $coaches = $team->personalCoaches;
            
            // For each student
            foreach ($students as $student) {
                // Create feedback from mentors
                foreach ($mentors as $mentor) {
                    $this->createFeedbackEntry($mentor, $student, $team, $skills);
                }
                
                // Create feedback from coaches
                foreach ($coaches as $coach) {
                    $this->createFeedbackEntry($coach, $student, $team, $skills);
                }
                
                // Create peer feedback from other students
                $otherStudents = $students->where('id', '!=', $student->id);
                foreach ($otherStudents as $otherStudent) {
                    $this->createFeedbackEntry($otherStudent, $student, $team, $skills);
                }
            }
        }
    }
    
    /**
     * Create a feedback entry.
     */
    private function createFeedbackEntry(User $sender, User $receiver, Team $team, $skills): void
    {
        // Randomly select a skill and practice
        $skill = $skills->random();
        $practice = $skill->practices->random();
        
        Feedback::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'team_id' => $team->id,
            'skill_id' => $skill->id,
            'practice_id' => $practice->id,
            'comments' => "Feedback from {$sender->name} to {$receiver->name} about {$skill->name} / {$practice->description}",
        ]);
    }
} 