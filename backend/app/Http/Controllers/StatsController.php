<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class StatsController extends Controller
{
    public function index()
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth();

        // Contacts
        $totalContacts    = Contact::count();
        $newContacts      = Contact::where('created_at', '>=', $monthStart)->count();

        // Leads
        $totalLeads   = Lead::count();
        $leadsByStage = Lead::selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        // Deals
        $totalDeals     = Deal::count();
        $dealsByStage   = Deal::selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');
        $pipelineValue  = Deal::whereNotIn('stage', ['won', 'lost'])->sum('value');
        $wonValue       = Deal::where('stage', 'won')->sum('value');
        $wonThisMonth   = Deal::where('stage', 'won')
            ->where('updated_at', '>=', $monthStart)->sum('value');

        // Tasks
        $tasksDueToday  = Task::whereDate('due_date', today())->where('status', '!=', 'completed')->count();
        $tasksOverdue   = Task::whereDate('due_date', '<', today())->where('status', '!=', 'completed')->count();
        $tasksPending   = Task::where('status', 'pending')->count();

        // Recent activities
        $recentActivities = Activity::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'type'        => $a->type,
                'title'       => $a->title,
                'user'        => $a->user?->name ?? 'System',
                'subject_type'=> class_basename($a->subject_type),
                'subject_id'  => $a->subject_id,
                'created_at'  => $a->created_at->diffForHumans(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'contacts' => [
                    'total'      => $totalContacts,
                    'this_month' => $newContacts,
                ],
                'leads' => [
                    'total'     => $totalLeads,
                    'by_stage'  => $leadsByStage,
                ],
                'deals' => [
                    'total'         => $totalDeals,
                    'by_stage'      => $dealsByStage,
                    'pipeline_value'=> (float) $pipelineValue,
                    'won_value'     => (float) $wonValue,
                    'won_this_month'=> (float) $wonThisMonth,
                ],
                'tasks' => [
                    'due_today' => $tasksDueToday,
                    'overdue'   => $tasksOverdue,
                    'pending'   => $tasksPending,
                ],
                'recent_activities' => $recentActivities,
            ],
        ]);
    }
}