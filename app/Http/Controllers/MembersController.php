<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Member;

class MembersController extends Controller
{
    public $edit = FALSE;
    public $messages = array();

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::user()->account_type != "ADMINISTRATOR" && Auth::user()->account_type != "SUPER ADMIN")
                return redirect('/');
            else return $next($request);
        });
    }

    public function list()
    {
        $data = array(
            'menu' => 'Members',
            'records' => Member::orderBy('name')->get(),
        );
        return view("system.members", $data);
    }

    public function toggle_visibility($recordid)
    {
        $info = Member::where('id', $recordid)->first();
        Member::where('id', $recordid)->update([
            'is_deleted' => ($info->is_deleted == 1 ? 0 : 1),
        ]);

        $this->messages[] = array('type' => 'success', 'text' => 'Successfully toggled visibility.');
        session()->flash('messages', $this->messages);
        return redirect()->route('members.list');
    }

    public function save_add(Request $request)
    {
        $this->edit = FALSE;
        return $this->_save_changes($request->all());
    }

    public function save_changes(Request $request)
    {
        $this->edit = TRUE;
        return $this->_save_changes($request->all());
    }

    public function _save_changes($data)
    {
        $the_id = $this->edit ? $data["member_id"] : 0;

        if ($this->save_member($the_id, $data) === false) {
            $this->messages[] = array('type' => 'danger', 'text' => 'Error ' . ($this->edit ? 'updating' : 'adding') . ' member.');
            session()->flash('messages', $this->messages);
            return redirect()->route('members.list');
        }

        $this->messages[] = array('type' => 'success', 'text' => 'Member ' . ($this->edit ? 'updated' : 'added') . ' successfully.');
        session()->flash('messages', $this->messages);
        return redirect()->route('members.list');
    }

    public function save_member(&$the_id, $data)
    {
        if ($this->edit) {
            $row = Member::where('id', $the_id)->update([
                'name' => $data['name'],
                'position' => $data['position'],
            ]);
            log_activity('Update Member', json_encode(['id' => $the_id, 'name' => $data['name']]));
            return $row;
        } else {
            $row = Member::create([
                'name' => $data['name'],
                'position' => $data['position'],
            ]);
            $the_id = $row->id;
            log_activity('Add Member', json_encode(['name' => $data['name']]));
            return $row;
        }
    }
}
