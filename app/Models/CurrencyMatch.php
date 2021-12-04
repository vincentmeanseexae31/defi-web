<?php

namespace App\Models;

class CurrencyMatch extends Model
{
    public $timestamps = false;

    protected static $USDTRate = null;

    protected $appends = [
        'legal_name',
        'currency_name',
        'market_from_name',
        'change',
        'volume',
        'now_price',
        'high',
        'low',
        'open',
        'close',
        'fiat_convert_cny',
        'logo',
        'plate_name',
    ];

    protected static $marketFromNames = [
        '无',
        '交易所',
        '火币接口',
        '机器人',
    ];

    public function legal()
    {
        return $this->belongsTo(Currency::class, 'legal_id', 'id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id')->withDefault();
    }

    public function quotation()
    {
        return $this->hasOne(CurrencyQuotation::class, 'legal_id', 'legal_id');
    }

    public function plate()
    {
        return $this->belongsTo(CurrencyPlate::class, 'plate_id', 'id');
    }

    public static function enumMarketFromNames()
    {
        return self::$marketFromNames;
    }

    public function getPlateNameAttribute()
    {
        return $this->plate->name ?? '';
    }

    public function getLogoAttribute()
    {
        return $this->currency()->value('logo') ?? '';
    }

    public function getSymbolAttribute()
    {
        return $this->getCurrencyNameAttribute() . '/' . $this->getLegalNameAttribute();
    }

    public function getMatchNameAttribute()
    {
        //return strtolower($this->getCurrencyNameAttribute() . $this->getLegalNameAttribute());
        $clone_currency_k = $this->currency()->value('clone_currency_k');
        if($clone_currency_k){
            $currency_name = $clone_currency_k;
        }else{
            $currency_name = $this->getCurrencyNameAttribute();
        }
        return strtolower($currency_name . $this->getLegalNameAttribute());
    }

    public function getLegalNameAttribute()
    {
        return $this->legal()->value('name');
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
    }

    public function getMarketFromNameAttribute($value)
    {
        return self::$marketFromNames[$this->attributes['market_from']];
    }

    public function getCreateTimeAttribute($value)
    {
        return $value === null ? '' : date('Y-m-d H:i:s', $value);
    }

    public function getDaymarketAttribute()
    {
        $legal_id = $this->attributes['legal_id'];
        $currency_id = $this->attributes['currency_id'];
        CurrencyQuotation::unguard();
        $quotation = CurrencyQuotation::firstOrCreate([
            'legal_id' => $legal_id,
            'currency_id' => $currency_id,
        ], [
            'match_id' => $this->attributes['id'],
            'change' => '',
            'volume' => 0,
            'open' => 0,
            'close' => 0,
            'high' => 0,
            'low' => 0,
            'now_price' => 0,
            'add_time' => time(),
        ]);
        CurrencyQuotation::reguard();
        return $quotation;
    }

    public function getChangeAttribute()
    {
        return $this->getDaymarketAttribute()->change;
    }

    public function getVolumeAttribute()
    {
        return $this->getDaymarketAttribute()->volume;
    }

    public function getNowPriceAttribute()
    {
        return $this->getDaymarketAttribute()->now_price ?? 0;
    }

    public function getOpenAttribute()
    {
        return $this->getDaymarketAttribute()->open ?? 0;
    }

    public function getCloseAttribute()
    {
        return $this->getDaymarketAttribute()->close ?? 0;
    }

    public function getHighAttribute()
    {
        return $this->getDaymarketAttribute()->high ?? 0;
    }

    public function getLowAttribute()
    {
        return $this->getDaymarketAttribute()->low ?? 0;
    }

    public static function getHuobiMatchs()
    {
        $currency_match = self::with(['legal', 'currency'])
            ->where('market_from', 2)
            ->get();
        $huobi_symbols = HuobiSymbol::pluck('symbol')->all();
        $currency_match->transform(function ($item, $key) {
            $item->addHidden('currency');
            $item->addHidden('legal');
            $item->append('match_name');
            return $item;
        });
        //过滤掉不在火币中的交易对
        $currency_match = $currency_match->filter(function ($value, $key) use ($huobi_symbols) {
            return in_array($value->match_name, $huobi_symbols);
        });
        return $currency_match;
    }

    public function getFiatConvertCnyAttribute()
    {
        $currency_id=$this->attributes['legal_id'];
        return Currency::getCnyPrice($currency_id);
    }
}