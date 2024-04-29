<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Channel;

use App\Http\Traits\ApiBranchKeyChecker;
use App\Http\Resources\ChannelResource;

class ChannelController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $channels = Channel::paginate(10);

        return ChannelResource::collection($channels);
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                Rule::unique((new Channel)->getTable())
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $channel = new Channel([
            'code' => $request->code,
            'name' => $request->name
        ]);
        $channel->save();

        return $this->successResponse(new ChannelResource($channel));
    }

    public function show(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        if(empty($id)) {
            return $this->validationError('id is required.');
        }

        $channel = Channel::where('id', $id)
            ->first();
        
        if(!empty($channel)) {
            return $this->successResponse(new ChannelResource($channel));
        } else {
            return $this->validationError('data not found');
        }
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        if(empty($id)) {
            return $this->validationError('id is required.');
        }

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                Rule::unique((new Channel)->getTable())->ignore($id)
            ],
            'name' => [
                'required'
            ]
        ]);

        $channel = Channel::where('id', $id)
            ->first();
        if(!empty($channel)) {
            $channel->update([
                'code' => $request->code,
                'name' => $request->name
            ]);

            return $this->successResponse(new ChannelResource($channel));
        } else {
            return $this->validationError('data not found!');
        }
    }
}
 