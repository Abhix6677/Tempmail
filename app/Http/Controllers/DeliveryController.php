<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Message;
use App\Models\Meta;
use App\Models\Page;
use App\Models\Stat;
use Exception;
use Illuminate\Http\Request;

class DeliveryController extends Controller {

    public function verify() {
        return response()->json([
            'success' => 'Valid Token'
        ]);
    }

    public function storeMessage(Request $request) {
        if (!($request->has('subject') && $request->has('from') && $request->has('to') && ($request->has('html') || $request->has('text')))) {
            return response()->json([
                'error' => 'Incomplete Data'
            ], 403);
        }
        try {
            Message::store($request);
            return response()->json([
                'success' => 'Message Stored'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function messages($page = 1, $limit = 15, $search = '') {
        $offset = ($page - 1) * $limit;
        $total = 0;
        $query = Message::query();
        if ($search) {
            $search = base64_decode($search);
            $query = $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', '%' . $search . '%')->orWhere('to', 'like', '%' . $search . '%')->orWhere('from', 'like', '%' . $search . '%');
            });
            $total = $query->count();
        } else {
            $total = Message::count();
        }
        $messages = $query->orderBy('created_at', 'DESC')->offset($offset)->limit($limit)->get();
        return response()->json([
            'success' => 'Message Fetched',
            'messages' => $messages,
            'total' => $total
        ]);
    }

    public function deleteMessage($message_id) {
        Message::find($message_id)->delete();
        return response()->json([
            'success' => 'Message Deleted'
        ]);
    }

    public function stats($filters = '') {
        if (trim($filters) == '') {
            $filters = [];
        } else {
            $filters = explode(',', trim($filters));
        }
        $data = [];
        if (count($filters) == 0 || in_array('most_popular_receiver', $filters)) {
            $temp = Message::select('to')->groupBy('to')->orderByRaw('COUNT(*) DESC')->first();
            $data['most_popular_receiver'] = ($temp) ? $temp->to : '';
            $data['most_popular_receiver_count'] = $data['most_popular_receiver'] ? Message::where('to', $data['most_popular_receiver'])->count() : 0;
        }
        if (count($filters) == 0 || in_array('most_popular_sender', $filters)) {
            $temp = Message::select('from')->groupBy('from')->orderByRaw('COUNT(*) DESC')->first();
            $data['most_popular_sender'] = ($temp) ? $temp->from : '';
            $data['most_popular_sender_count'] = $data['most_popular_sender'] ? Message::where('from', $data['most_popular_sender'])->count() : 0;
        }
        if (count($filters) == 0 || in_array('total_email_ids', $filters)) {
            $data['total_email_ids'] = number_format(Stat::where('type', 'emails_created')->sum('count'));
            $data['email_ids_in_last_7_days'] = Log::count();
        }
        if (count($filters) == 0 || in_array('total_messages', $filters)) {
            $data['total_messages'] = number_format(Stat::where('type', 'messages_received')->sum('count'));
            $data['current_messages_on_tmail'] = Message::count();
        }
        if (count($filters) == 0 || in_array('total_unique_ips', $filters)) {
            $record = Log::select('ip')->groupBy('ip')->orderByRaw('COUNT(*) DESC')->get();
            $data['total_unique_ips'] = $record->count();
            $data['ip_with_most_usage'] = $record->first()->ip;
        }
        if (count($filters) == 0 || in_array('unread_messages', $filters)) {
            $data['unread_messages'] = Message::where('is_seen', false)->count();
            $data['unread_messages_percentage'] = 0;
            $total_messages = Message::count();
            if ($total_messages) {
                $data['unread_messages_percentage'] = intval(($data['unread_messages'] / $total_messages) * 100) . '%';
            }
        }
        if (count($filters) == 0 || in_array('latest_messages', $filters)) {
            $data['latest_messages'] = Message::select('id', 'subject', 'from', 'to', 'created_at')->orderBy('created_at', 'DESC')->limit(15)->get();
        }
        if (count($filters) == 0 || in_array('total_pages', $filters)) {
            $data['total_pages'] = Page::count();
        }
        if (count($filters) == 0 || in_array('total_domains', $filters)) {
            $data['total_domains'] = count(config('app.settings.domains'));
        }
        if (count($filters) == 0 || in_array('version', $filters)) {
            $data['version'] = config('app.settings.version');
        }
        if (count($filters) == 0 || in_array('theme', $filters)) {
            $data['theme'] = config('app.settings.theme');
        }
        return response()->json([
            'success' => 'Stats Fetched Successfully',
            'data' => $data
        ]);
    }
}
