webpackJsonp([9],{"1WCA":function(t,s){},bhqy:function(t,s,e){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var a=e("bOdI"),i=e.n(a),l={data:function(){var t;return t={legals:[],list:[],type:"sell",id:0,page:1,classify:this.$t("td.buy"),name:"CNY",pages:0,prices:0,minNum:0,maxNum:0,max:0,names:"CNY",time:"60",shows:!1,types:"trade",nums:"",surplus_number:0,totalNums:"0.00",ID:"",money_type:"",name01:"CNY",more:!0},i()(t,"surplus_number",0),i()(t,"user_legal_balance",0),i()(t,"bgcolor",["#5d8cc2","#6d78a8","#a2b240","#61b88e","#e35744","#a16c92","#66756e"]),i()(t,"interval",function(){}),i()(t,"coin","CNY"),t},created:function(){var t=window.localStorage.getItem("token")||"";this.token=t,this.getCoins()},filters:{toFixeds:function(t){return(t=Number(t)).toFixed(3)},strFirst:function(t){return t.substr(0,1)}},methods:{close:function(){this.shows=!1,clearInterval(this.interval),this.time=60},getCoins:function(){var t=this;this.$http({url:"/api/currency/list"}).then(function(s){if("ok"==s.data.type){var e=s.data.message.legal;if(e.length){t.legals=e;var a=e[0].id;t.name=e[0].name,t.getList("sell",a,1)}}})},getList:function(t,s,e){var a=this;this.type=t,this.id=s,this.page=e;var i=layer.load();this.$http({url:"/api/legal_deal_platform",params:{type:t,page:e,currency_id:s},headers:{Authorization:this.token}}).then(function(t){if("ok"==t.data.type){layer.close(i);var s=t.data.message.data;if(s.length>0){for(var e=0;e<s.length;e++)s[e].bgcolor=a.bgcolor[Math.floor(6*Math.random()+1)-1];a.list=a.list.concat(s),a.page=a.page+1,a.more=!0}else a.more=!1}})},getMore:function(){this.getList(this.type,this.id,this.page)},changeClassify:function(t,s,e){this.list=[],this.id=t,1==s?(this.type="sell",this.classify=this.$t("td.buy")):(this.type="buy",this.classify=this.$t("td.sell")),this.name=e,this.getList(this.type,t,1)},buySell:function(t,s,e,a,i,l,n){"sell"==i?this.money_type=this.$t("td.buy"):"buy"==i&&(this.money_type=this.$t("td.sell"));var r=this;r.nums="",r.shows=!0,r.ID=a,r.time=60,document.getElementsByTagName("body")[0].className="body",r.prices=t,r.minNum=s,r.maxNum=e,r.surplus_number=l,r.coin=n,r.name01=n,r.interval=setInterval(function(){r.time--,r.time<=0&&(r.shows=!1,document.body.removeAttribute("class","body"),clearInterval(r.interval),r.time=60)},1e3),r.$http({url:"/api/legal_deal_info",method:"get",params:{id:a},headers:{Authorization:localStorage.getItem("token")}}).then(function(t){"ok"==t.data.type&&(r.surplus_number=t.data.message.surplus_number,r.user_legal_balance=(t.data.message.user_legal_balance-0).toFixed(3))})},tabClassify:function(t){this.nums="",1==t?(this.types="trade",this.name01=this.coin):(this.types="num",this.name01=this.name)},allMoney:function(){"buy"==this.type?"trade"==this.types?this.nums=(this.user_legal_balance*this.prices).toFixed(3):this.nums=(this.user_legal_balance-0).toFixed(3):"num"==this.types?this.nums=(this.surplus_number-0).toFixed(3):this.nums=(this.maxNum-0).toFixed(3)},buyOrder:function(){this.shows=!1;var t=this,s="money";window.localStorage.getItem("user_id"),window.localStorage.getItem("token");layer.prompt({title:t.$t("td.pwd"),formType:1,btn:[t.$t("td.confirm"),t.$t("td.canceil")]},function(e,a){if(t.password=e,t.nums){s="trade"==t.types?"money":"number";var i={id:t.ID,means:s,value:t.nums,password:t.password};t.buyHttp("/api/do_legal_deal",i,function(s){"ok"==s.data.type?"sell"==s.data.message.data.type?(layer.msg(s.data.message.msg),setTimeout(function(){t.$router.push({path:"/legalPay",query:{id:s.data.message.data.id}})},500)):(layer.msg(s.data.message.msg),setTimeout(function(){t.$router.push({path:"/legalPayDetail",query:{id:s.data.message.data.id}})},500)):layer.msg(s.data.message)})}else"sell"==t.type?"trade"==t.types?layer.msg(t.$t("td.buytotal")):layer.msg(t.$t("td.buyallnum")):"trade"==t.types?layer.msg(t.$t("td.selltotal")):layer.msg(t.$t("td.sellallnum"));layer.close(a)})},buyHttp:function(t,s,e){var a=this;a.$http({url:t,method:"post",data:s,headers:{Authorization:localStorage.getItem("token")}}).then(function(t){"ok"==t.data.type?e&&e(t):(layer.msg(t.data.message),"997"==t.data.type&&setTimeout(function(){a.$router.push("/userSetting")},1500),"998"==t.data.type&&setTimeout(function(){a.$router.push("/authentication")},1500))})},recordList:function(){this.$router.push({path:"/LegalRecord",query:{id:this.id}})}}},n={render:function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("div",{staticClass:"white",attrs:{id:"legaltrade-box"}},[a("div",{staticClass:"buy-sell flex"},[a("div",{staticClass:"buy-box bod_rc"},[a("div",{staticClass:"title"},[t._v(t._s(t.$t("td.buy")))]),t._v(" "),a("ul",{class:["flex",{now:"sell"==t.type}]},t._l(t.legals,function(s,e){return a("li",{key:e,class:{current:s.id==t.id},on:{click:function(e){t.changeClassify(s.id,1,s.name)}}},[t._v(t._s(s.name))])}))]),t._v(" "),a("div",{staticClass:"sell-box"},[a("div",{staticClass:"title"},[t._v(t._s(t.$t("td.sell")))]),t._v(" "),a("ul",{class:["flex",{now:"buy"==t.type}]},t._l(t.legals,function(s,e){return a("li",{key:e,class:{current:s.id==t.id},on:{click:function(e){t.changeClassify(s.id,2,s.name)}}},[t._v(t._s(s.name))])}))]),t._v(" "),a("div",{staticClass:"record light_blue",on:{click:function(s){t.recordList()}}},[t._v(t._s(t.$t("fat.orderLog")))])]),t._v(" "),a("div",{staticClass:"list-box"},[a("div",{staticClass:"list-title flex ft14 gray9 tc"},[a("div",{staticClass:"tl"},[t._v(t._s(t.$t("fat.shoper")))]),t._v(" "),a("div",[t._v(t._s(t.$t("td.num")))]),t._v(" "),a("div",[t._v(t._s(t.$t("td.limit")))]),t._v(" "),a("div",[t._v(t._s(t.$t("td.price")))]),t._v(" "),a("div",[t._v(t._s(t.$t("td.method")))]),t._v(" "),a("div",{staticClass:"tr"},[t._v(t._s(t.$t("td.do")))])]),t._v(" "),a("ul",{staticClass:"list"},t._l(t.list,function(s,i){return 1==s.is_shelves?a("li",{key:i,staticClass:"bdb flex bod_bc ft14 tc"},[a("div",{staticClass:"flex alcenter"},[a("div",{staticClass:"head",style:{background:s.bgcolor}},[t._v(t._s(t._f("strFirst")(s.seller_name||"")))]),t._v(" "),a("div",{staticClass:"flex column"},[a("span",{staticClass:"light_blue bold"},[t._v(t._s(s.seller_name))])])]),t._v(" "),a("div",{staticClass:"flex alcenter center"},[t._v(t._s(t._f("toFixeds")(s.surplus_number||"0.000"))+"  "+t._s(s.currency_name))]),t._v(" "),a("div",{staticClass:"flex alcenter center"},[t._v(t._s(t._f("toFixeds")(s.limitation.min||"0.000"))+" "+t._s(s.coin_code)+" - "+t._s(t._f("toFixeds")(s.limitation.max||"0.000"))+" "+t._s(s.coin_code))]),t._v(" "),a("div",{staticClass:"flex alcenter price ft16 bold center"},[t._v(t._s(t._f("toFixeds")(s.price||"0.000"))+" "+t._s(s.coin_code))]),t._v(" "),a("div",{staticClass:"flex alcenter center"},[t._l(s.pay_ways_arr,function(s){return"ali_pay"==s?a("img",{attrs:{src:e("F43K")}}):t._e()}),t._v(" "),t._l(s.pay_ways_arr,function(s){return"we_chat"==s?a("img",{staticClass:"ml10",attrs:{src:e("PVjh")}}):t._e()}),t._v(" "),t._l(s.pay_ways_arr,function(s){return"bank"==s?a("img",{staticClass:"ml10",attrs:{src:e("ufz/")}}):t._e()})],2),t._v(" "),a("div",{staticClass:"flex alcenter end",on:{click:function(e){t.buySell(s.price,s.limitation.min,s.limitation.max,s.id,s.type,s.surplus_number,s.coin_code)}}},[a("button",{staticClass:"curPer"},[t._v(t._s(t.classify)+t._s(t.name))])])]):t._e()})),t._v(" "),t.list.length&&t.more?a("div",{staticClass:"more tc mt20 curPer",on:{click:function(s){t.getMore()}}},[t._v(t._s(t.$t("td.more")))]):a("div",{staticClass:"more tc mt20"},[t._v(t._s(t.$t("td.nomore")))])]),t._v(" "),a("div",{directives:[{name:"show",rawName:"v-show",value:t.shows,expression:"shows"}],staticClass:"modal"},[a("div",{staticClass:"mask",on:{click:function(s){t.closeBtn()}}}),t._v(" "),a("div",{staticClass:"content"},[a("div",{staticClass:"content-list"},[a("p",{staticClass:"close tr curPer",on:{click:function(s){t.close()}}},[t._v("X")]),t._v(" "),a("p",{staticClass:"title"},[t._v(t._s(t.classify)+t._s(t.name))]),t._v(" "),a("p",{staticClass:"price"},[t._v(t._s(t.$t("td.price"))+t._s(t._f("toFixeds")(t.prices||"0.000"))+" ")]),t._v(" "),a("div",{staticClass:"trade"},[a("p",{class:["trade-name",{active:"trade"==t.types}],on:{click:function(s){t.tabClassify(1)}}},[t._v(t._s(t.coin)+t._s(t.$t("td.trade")))]),t._v(" "),a("p",{class:["trade-num",{active:"num"==t.types}],on:{click:function(s){t.tabClassify(2)}}},[t._v(t._s(t.classify)+t._s(t.$t("td.num")))])]),t._v(" "),a("div",{staticClass:"totals-num"},["trade"==t.types?a("input",{directives:[{name:"model",rawName:"v-model",value:t.nums,expression:"nums"}],staticClass:"number",attrs:{type:"number",placeholder:t.$t("td.inwant")+t.money_type+t.$t("td.total")},domProps:{value:t.nums},on:{input:function(s){s.target.composing||(t.nums=s.target.value)}}}):a("input",{directives:[{name:"model",rawName:"v-model",value:t.nums,expression:"nums"}],staticClass:"number",attrs:{type:"number",placeholder:t.$t("td.inwant")+t.money_type+t.$t("td.num")},domProps:{value:t.nums},on:{input:function(s){s.target.composing||(t.nums=s.target.value)}}}),t._v(" "),"sell"==t.type?a("button",{staticClass:"all curPer",attrs:{type:"button"},on:{click:function(s){t.allMoney()}}},[t._v(t._s(t.$t("td.allbuy")))]):a("button",{staticClass:"all curPer",attrs:{type:"button"},on:{click:function(s){t.allMoney()}}},[t._v(t._s(t.$t("td.allsell")))]),t._v(" "),a("span",{staticClass:"name"},[t._v(t._s(t.name01))])]),t._v(" "),a("div",{staticClass:"maxnum"},[t._v(t._s(t.$t("td.limit"))+t._s(t._f("toFixeds")(t.minNum||"0.000"))+"-"+t._s(t._f("toFixeds")(t.maxNum||"0.000")))]),t._v(" "),a("div",{staticClass:"trade-totals"},[a("p",{staticClass:"total-price"},[t._v(t._s(t.$t("td.tradeTotal")))]),t._v(" "),"trade"==t.types?a("p",{staticClass:"prices"},[t._v(t._s(t._f("toFixeds")(t.nums||"0.000"))+" "+t._s(t.coin))]):a("p",{staticClass:"prices"},[t._v(t._s(t._f("toFixeds")(t.nums*t.prices||"0.000"))+" "+t._s(t.coin))])]),t._v(" "),a("div",{staticClass:"btns"},[a("p",{staticClass:"cannel"},[t._v(t._s(t.time)+t._s(t.$t("td.doceil")))]),t._v(" "),a("button",{staticClass:"comfirm curPer",attrs:{type:"button"},on:{click:function(s){t.buyOrder()}}},[t._v(t._s(t.$t("td.place")))])])])])])])},staticRenderFns:[]};var r=e("VU/8")(l,n,!1,function(t){e("1WCA")},null,null);s.default=r.exports}});
//# sourceMappingURL=9.33aae3150d9f1001c32e.js.map