<?php



Route::get('code', function(){
	$url= 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxc6e2459bdea9cd72&redirect_uri=http://kvczys.natappfree.cc/BiYe2/public/users/getopenid&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
	return redirect($url);
});
