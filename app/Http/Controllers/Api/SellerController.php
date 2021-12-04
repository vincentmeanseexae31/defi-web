<?php

namespace App\Http\Controllers\Api;

use App\Models\UserReal;
use App\Models\Users;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $currency_id = $request->get('currency_id', 0);
        if (empty($currency_id)) {
            return $this->error('参数错误');
        }
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error('无此币种');
        }
        if (empty($currency->is_legal)) {
            return $this->error('该币不是法币');
        }
        $results = Seller::where('currency_id', $currency->id)->orderBy('id', 'desc')->paginate($limit);
        return $this->pageDate($results);
    }


    public function applyInfo()
    {
        $banks = Bank::get()->toArray();
        $currencies = Currency::where('is_legal', 1)->orderBy('id', 'desc')->get()->toArray();

        $data['banks'] = $banks;
        $data['currency'] = $currencies;

        return $this->success($data);
    }

    //申请商家
    public function postAdd(Request $request)
    {

        $name = $request->get('name', '');
        $mobile = $request->get('mobile', '');
        $currency_id = $request->get('currency_id', '');
        $seller_balance = $request->get('seller_balance', 0);
        $wechat_nickname = $request->get('wechat_nickname', '');
        $wechat_account = $request->get('wechat_account', '');
        $wechat_collect = $request->get('wechat_collect', '');
        $ali_nickname = $request->get('ali_nickname', '');
        $ali_account = $request->get('ali_account', '');
        $alipay_collect = $request->get('alipay_collect', '');
        $bank_id = $request->get('bank_id', 0);
        $bank_account = $request->get('bank_account', '');
        $bank_address = $request->get('bank_address', '');

        if (empty($name) || empty($mobile) || empty($currency_id)) {
            return $this->error('信息必填');
        }
        if (empty($wechat_collect) && empty($alipay_collect) && empty($bank_account)) {
            return $this->error('支付方式必填');
        }

        $user_id = Users::getUserId();
        $self = Users::find($user_id);

        $real = UserReal::where('user_id', $self->id)
            ->where('review_status', 2)
            ->first();
        if (empty($real)) {
            return $this->error('此用户还未通过实名认证');
        }
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error('币种不存在');
        }
        if (empty($currency->is_legal)) {
            return $this->error('该币不是法币');
        }
        $has = Seller::where('name', $name)
            ->where('user_id', '<>', $self->id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!empty($has)) {
            return $this->error('此法币 ' . $name . ' 商家名称已存在');
        }
        $has_user = Seller::where('user_id', $self->id)->where('currency_id', $currency_id)->first();
        if (!empty($has_user)) {
            return $this->error('您已是此法币商家');
        }

        $acceptor = new Seller();
        $acceptor->create_time = time();
        $acceptor->user_id = $self->id;
        $acceptor->name = $name;
        $acceptor->mobile = $mobile;
        $acceptor->currency_id = $currency_id;
        $acceptor->seller_balance = floatval($seller_balance);
        $acceptor->wechat_nickname = $wechat_nickname;
        $acceptor->wechat_account = $wechat_account;
        $acceptor->wechat_collect = $wechat_collect;
        $acceptor->ali_nickname = $ali_nickname;
        $acceptor->alipay_collect = $alipay_collect;
        $acceptor->ali_account = $ali_account;
        $acceptor->bank_id = intval($bank_id);
        $acceptor->bank_account = $bank_account;
        $acceptor->bank_address = $bank_address;
        $acceptor->status = 0;
        try {
            $acceptor->save();
            return $this->success('申请成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}