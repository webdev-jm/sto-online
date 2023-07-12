<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Http\Requests\ChannelAddRequest;
use App\Http\Requests\ChannelUpdateRequest;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class ChannelController extends Controller
{
    use AccountChecker;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // check account
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $search = trim($request->get('search'));

        $channels = Channel::orderBy('created_at', 'DESC')
            ->when(auth()->user()->can('channel restore'), function($query) {
                $query->withTrashed();
            })
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.channels.index')->with([
            'channels' => $channels,
            'account' => $account,
            'account_branch' => $account_branch,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // check account
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.channels.create')->with([
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ChannelAddRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ChannelAddRequest $request)
    {
        // check account
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $channel = new Channel([
            'account_id' => $account->id,
            'account_branch_id' => $account_branch->id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $channel->save();

        // logs
        activity('create')
        ->performedOn($channel)
        ->log(':causer.name has created channel ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('channel.index')->with([
            'message_success' => 'Channel '.$channel->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // check account
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $channel = Channel::findOrFail(decrypt($id));

        return view('pages.channels.show')->with([
            'channel' => $channel,
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // check account
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $channel = Channel::findOrFail(decrypt($id));

        return view('pages.channels.edit')->with([
            'channel' => $channel,
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ChannelUpdateRequest  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function update(ChannelUpdateRequest $request, $id)
    {
        // check account
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $channel = Channel::findOrFail(decrypt($id));
        $changes_arr['old'] = $channel->getOriginal();

        $channel->update([
            'code' => $request->code,
            'name' => $request->name
        ]);

        $changes_arr['changes'] = $channel->getChanges();

        // logs
        activity('update')
        ->performedOn($channel)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated channel ['.$account->short_name.'] :subject.code :subject.name');

        return back()->with([
            'message_success' => 'Channel '.$channel->name.' was updated.'
        ]);
    }

    public function restore($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $channel = Channel::withTrashed()->findOrFail(decrypt($id));

        $channel->restore();

        activity('restore')
            ->performedOn($channel)
            ->log(':causer.name has restored channel '.$channel->name);

        return back()->with([
            'message_success' => 'Channel '.$channel->name.' has been restored.'
        ]);
    }
}
