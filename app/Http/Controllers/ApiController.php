<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Setting;
use App\Services\TMail;
use Exception;

class APIController extends Controller {

    public function domains($key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            return Setting::pick('domains');
        } else {
            return abort(401);
        }
    }

    public function email($email = '', $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($email) {
                try {
                    $split = explode('@', $email);
                    if (in_array($split[0], config('app.settings.forbidden_ids'))) {
                        return response()->json('Username not allowed', 406);
                    }
                    if (strlen($split[0]) < config('app.settings.custom.min') || strlen($split[0]) > config('app.settings.custom.max')) {
                        return response()->json('Username length cannot be less than' . ' ' . config('app.settings.custom.min') . ' ' . 'and greator than' . ' ' . config('app.settings.custom.max'), 406);
                    }
                    return TMail::createCustomEmail($split[0], $split[1]);
                } catch (Exception $e) {
                    // Fallback to Gmail dot-alias instead of legacy random generator
                    $index = session('dot_index', 0) + 1;
                    session(['dot_index' => $index]);
                    return TMail::generateDotAliasEmail($index, false);
                }
            } else {
                $index = session('dot_index', 0) + 1;
                session(['dot_index' => $index]);
                return TMail::generateDotAliasEmail($index, false);
            }
        } else {
            return abort(401);
        }
    }

    public function messages($email = '', $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($email) {
                try {
                    $data = [];
                    $split = explode('@', $email);
                    if (in_array($split[0], config('app.settings.forbidden_ids'))) {
                        return response()->json('Username not allowed', 406);
                    }
                    if (strlen($split[0]) < config('app.settings.custom.min') || strlen($split[0]) > config('app.settings.custom.max')) {
                        return response()->json('Username length cannot be less than' . ' ' . config('app.settings.custom.min') . ' ' . 'and greator than' . ' ' . config('app.settings.custom.max'), 406);
                    }
                    $response = TMail::getMessages($email);
                    $data = $response['data'];
                    TMail::incrementMessagesStats(count($response['notifications']));
                    return $data;
                } catch (\Exception $e) {
                    return abort(500);
                }
            } else {
                return abort(204);
            }
        } else {
            return abort(401);
        }
    }

    public function message($message_id = 0, $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($message_id) {
                if (config('app.settings.engine') == 'delivery') {
                    return Message::find($message_id);
                } else {
                    try {
                        $connection = TMail::connectMailBox();
                        $mailbox = $connection->getMailbox('INBOX');
                        $message = $mailbox->getMessage($message_id);
                        $data = TMail::formatMessage($message);
                        return $data['message'];
                    } catch (\Exception $e) {
                        return abort(500);
                    }
                }
            } else {
                return abort(204);
            }
        } else {
            return abort(401);
        }
    }

    public function delete($message_id = 0, $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($message_id) {
                if (config('app.settings.engine') == 'delivery') {
                    Message::find($message_id)->delete();
                } else {
                    try {
                        $connection = TMail::connectMailBox();
                        $mailbox = $connection->getMailbox('INBOX');
                        $mailbox->getMessage($message_id)->delete();
                        $connection->expunge();
                    } catch (\Exception $e) {
                        return abort(500);
                    }
                }
            } else {
                return abort(204);
            }
        } else {
            return abort(401);
        }
    }
}
