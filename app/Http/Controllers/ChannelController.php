<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Http\Requests\ChannelAddRequest;
use App\Http\Requests\ChannelUpdateRequest;

use Illuminate\Support\Facades\Session;

class ChannelController extends Controller
{
    private function checkAccount() {
        $account = Session::get('account');
        if(!isset($account) || empty($account)) {
            return redirect()->route('home')->with([
                'error_message' => 'Please select an account.'
            ]);
        }
    
        return $account;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $channels = Channel::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.channels.index')->with([
            'channels' => $channels,
            'account' => $account
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
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        return view('pages.channels.create')->with([
            'account' => $account
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
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $channel = new Channel([
            'account_id' => $account->id,
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
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $channel = Channel::findOrFail(decrypt($id));

        return view('pages.channels.show')->with([
            'channel' => $channel,
            'account' => $account
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
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $channel = Channel::findOrFail(decrypt($id));

        return view('pages.channels.edit')->with([
            'channel' => $channel,
            'account' => $account
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
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel)
    {
        //
    }
}
