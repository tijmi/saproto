<?php

namespace Proto\Http\Controllers;

use Auth;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Proto\Models\QrAuthRequest;
use Proto\Models\RfidCard;
use Redirect;

class RfidCardController extends Controller
{
    /**
     * @param Request $request
     * @return array This method returns raw HTML and is intended to be used via AJAX!
     * @throws Exception
     */
    public function store(Request $request)
    {
        switch ($request->input('credentialtype')) {
            case 'qr':
                $qrAuthRequest = QrAuthRequest::where('auth_token', $request->input('credentials'))->first();
                if (! $qrAuthRequest) {
                    return ['ok' => false, 'text' => 'Invalid authentication token.'];
                }
                $user = $qrAuthRequest->authUser();
                if (! $user) {
                    return ['ok' => false, 'text' => "QR authentication hasn't been completed."];
                }
                break;

            default:
                return ['ok' => false, 'text' => 'Invalid credential type.'];
        }

        if (! $user->is_member) {
            return ['ok' => false, 'text' => 'You must be a member to use the OmNomCom.'];
        }

        $uid = $request->input('card');
        if (strlen($uid) == 0) {
            return ['ok' => false, 'text' => 'Empty card UID provided. Did you scan your card properly?'];
        }

        $card = RfidCard::where('card_id', $uid)->first();
        if ($card) {
            if ($card->user->id == $user->id) {
                return ['ok' => false, 'text' => 'This card is already registered to you!'];
            } else {
                return ['ok' => false, 'text' => 'This card is already registered to someone.'];
            }
        } else {
            $card = RfidCard::create([
                'user_id' => $user->id,
                'card_id' => $uid,
            ]);
            $card->save();

            return ['ok' => true, 'text' => 'This card has been successfully registered to '.$user->name];
        }
    }

    /**
     * @param $id
     * @return View
     */
    public function edit($id)
    {
        /** @var RfidCard $rfid */
        $rfid = RfidCard::findOrFail($id);
        if (($rfid->user->id != Auth::id()) && (! Auth::user()->can('board'))) {
            abort(403);
        }

        return view('users.rfid.edit', ['card' => $rfid]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        /** @var RfidCard $rfid */
        $rfid = RfidCard::findOrFail($id);
        if ($rfid->user->id != Auth::id()) {
            abort(403);
        }

        $rfid->name = $request->input('name');
        $rfid->save();

        $request->session()->flash('flash_message', 'Your RFID card has been updated.');
        return Redirect::route('user::dashboard');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Request $request, $id)
    {
        /** @var RfidCard $rfid */
        $rfid = RfidCard::findOrFail($id);
        if ($rfid->user->id != Auth::id()) {
            abort(403);
        }
        $rfid->delete();

        $request->session()->flash('flash_message', 'Your RFID card has been deleted.');
        return Redirect::back();
    }
}
