<?php
/**
 * �L�����e�t�H�[��
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Response;
use Cookie;
use Log;
use Validator;
use Auth;
use Session;
use DB;
use Carbon\Carbon;
use GuzzleHttp;

class AdFormController extends Controller
{
    /**
     * index�A�N�V����
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        try {
            // ���[�U�����擾
            $user = $this->jms_login_auth()->getUser();
            Log::debug(__FUNCTION__.' : login user ->'.print_r($user, 1));

            // �Ǝ���`JS
            $assetJs = [
                '/js/inquiry.js'
            ];

            return view(
                'adForm', [
                    'user'    => $user,     // JMS���[�U���
                    'assetJs' => $assetJs,  // �Ǝ���`JS
                ]
            );
        }
        // �����O�C���̏ꍇ�A��O�Ƃ��ăL���b�`����
        catch (\Exception $e) {
            // �Z�b�V�����ɖ߂��URL���Z�b�g
            Session::put('callback_url', '/adForm');

            Log::debug(print_r($e->getMessage(), 1));
            return redirect()->to('/login/jms');
        }
    }

    /**
     * ���e�����A�N�V����
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        Log::debug('$request -> '.print_r($request->input(), 1));

        // �p�����[�^�擾
        $inquiry_text = $request->input('inquiry_text');
        $reply_type   = $request->input('reply_type');

        // �ԐM�^�C�v�̃Z�b�g
        if ($reply_type == 'twitter') {
            $contact_id_label = 'Twitter ID';
            $type = 1;

            // �o���f�[�V�����G���[���̃��b�Z�[�W���Z�b�g
            $messages = [
                'reply_type.required'   => '�A����͕K�{���ڂł��B',
                'reply_type.in'         => '�A����̒l���s���ł��B',
                'contact_id.required'   => $contact_id_label.'�͕K�{���ڂł��B',
                'contact_id.twitter'  => '���͂���Twitter ID�͑��݂��܂���B',
                'inquiry_text.required' => '���₢���킹���e�͕K�{���ڂł��B',
                'inquiry_text.repeatlinefeed' => '���s�̑��p�͂��������������B(�A��������s��2��܂ŋ��e���܂�)'
            ];

            // �o���f�[�V��������
            Validator::make($request->all(), [
                'reply_type'   => 'required|in:twitter,discord',
                'contact_id'   => 'required',
                'inquiry_text' => 'required|repeatlinefeed',
            ], $messages)->validate();

        }
        elseif ($reply_type == 'discord') {
            $contact_id_label = 'Discord ID';
            $type = 2;

            // �o���f�[�V�����G���[���̃��b�Z�[�W���Z�b�g
            $messages = [
                'reply_type.required'   => '�A����͕K�{���ڂł��B',
                'reply_type.in'         => '�A����̒l���s���ł��B',
                'contact_id.required'   => $contact_id_label.'�͕K�{���ڂł��B',
                'contact_id.discordid'  => 'Discord ID�� �����Ɂu#�����v����͂��Ă��������B(��:user_name#1234)',
                'inquiry_text.required' => '���₢���킹���e�͕K�{���ڂł��B',
                'inquiry_text.repeatlinefeed' => '���s�̑��p�͂��������������B(�A��������s��2��܂ŋ��e���܂�)',
            ];

            // �o���f�[�V��������
            Validator::make($request->all(), [
                'reply_type'   => 'required|in:twitter,discord',
                'contact_id'   => 'required|discordid',
                'inquiry_text' => 'required|repeatlinefeed',
            ], $messages)->validate();
        }
        else {
            $contact_id_label = 'ID';
            $type = 9;
        }

        // �N�b�L�[�������ē���΁A���e�s��
        if (!empty($_COOKIE["inquiry"])) {
            Log::debug('inquiry cookie -> '.print_r($_COOKIE["inquiry"], 1));
            return redirect('inquiryForm')->withErrors( "���₢���킹�̘A�����e�͂��������������B");
        }

        // ���e����
        try {
            // ���[�U�����擾
            $user = $this->jms_login_auth()->getUser();
            Log::debug(__FUNCTION__ . ' : login user -> ' . print_r($user, 1));

            // Discord Bot��post���N�G�X�g
            $discord_content = "**[".$user['preferred_username']."]**\n".$inquiry_text;
            $client = new GuzzleHttp\Client();
            $client->post(
                env('DISCORD_INQUIRY_URL'),
                ['json' => ['content' => $discord_content]]
            );

            // �₢���킹�f�[�^�ۑ�
            DB::table('inquiry')->insert([
                'name' => $user['preferred_username'],
                'inquiry_text' => $inquiry_text,
                'inquiry_date' => Carbon::now(),
                'reply_type'   => $type,
                'contact_id'   => $request->input('contact_id'),
                'solved_flg'   => 0,
                'delete_flg'   => 0,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]);

            // ��d���e�h�~��cookie�𐶐�
            $cookie = \Cookie::make('inquiry', md5(uniqid(mt_rand(), true)), 1);

            // cookie���N���C�A���g(�u���E�U)�֕ۑ� + ���e������ʂ֑J��
            return redirect('/thanks')->withCookie($cookie)->with('message', '�L�����e���肪�Ƃ��������܂����B���e�͉^�c�`�[���ɂ���ĐR������A���ʂ��o����A�A�����s���܂��B');
        }
            // ��O�L���b�`
        catch (\Exception $e) {
            // �Z�b�V�����ɖ߂��URL���Z�b�g
            Session::put('callback_url', '/adForm');

            Log::debug(print_r($e->getMessage(), 1));
            return redirect()->to('/login/jms');
        }

    }

}
