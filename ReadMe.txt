<?php
// DateTimeJp.php を読み込む
require_once('DateTimeJp.php');

// Excptionを返すことがあるため、try～catch内で使用する
try {

	/* DateTimeJpクラスの生成 */

	// 現在時刻でオブジェクトを生成
	$dtjp = new DateTimeJp();

	// 年月日を指定してオブジェクトを生成
	$dtjp2 = new DateTimeJp('2011/08/09');

	// 年月日 時分秒 マイクロ秒を指定してオブジェクトを生成
	$dtjp3 = new DateTimeJp('2011/08/09 00:36:25.179467');

	// 「+1 month」の様に、strtotime()で使用できる日付文字列でも
	// オブジェクトを生成できる
	$dtjp4 = new DateTimeJp('+1 month');


	/* DateTimeJpクラスのプロパティ */

	// プロパティに日付文字列でフォーマットした値を持つ
	// 参考 http://jp2.php.net/manual/ja/function.date.php の「パラメータ」の部分
	//
	// d, D, j, l, N, S, w, z, W, F, m, M, n, t, L, o, Y, y,
	// a, A, B, g, G, h, H, i, s, u, e, I, O, P, T, Z, c, r, U

	// $dtjp3->Y : 2011
	// $dtjp3->m : 08
	// $dtjp3->d : 09
	// $dtjp3->H : 00
	// $dtjp3->i : 36
	// $dtjp3->s : 25、といった風に。

	// また、定義済み定数でフォーマットした値もプロパティとして持つ
	// 参考 http://jp2.php.net/manual/ja/class.datetime.php#datetime.constants.types
	// 
	// $dtjp3->ATOM		: 2011-08-09T00:36:25+09:00
	// $dtjp3->COOKIE	: Tuesday, 09-Aug-11 00:36:25 JST
	// $dtjp3->ISO8601	: 2011-08-09T00:36:25+0900
	// $dtjp3->RFC822	: Tue, 09 Aug 11 00:36:25 +0900
	// $dtjp3->RFC850	: Tuesday, 09-Aug-11 00:36:25 JST
	// $dtjp3->RFC1036	: Tue, 09 Aug 11 00:36:25 +0900
	// $dtjp3->RFC1123	: Tue, 09 Aug 2011 00:36:25 +0900
	// $dtjp3->RFC2822	: Tue, 09 Aug 2011 00:36:25 +0900
	// $dtjp3->RFC3339	: 2011-08-09T00:36:25+09:00
	// $dtjp3->RSS		: Tue, 09 Aug 2011 00:36:25 +0900
	// $dtjp3->W3C		: 2011-08-09T00:36:25+09:00

	// その他のプロパティ

	// マイクロ秒(最大、小数点以下6位まで保持)
	// $dtjp3->mSec		: 0.179467

	// 日本の祝日判定(祝日名 or false)
	// 
	// 祝日名：
	// 元日, 成人の日, 建国記念の日, 春分の日, 昭和の日, 憲法記念日,
	// みどりの日, こどもの日, 海の日, 敬老の日, 秋分の日, 体育の日,
	// 文化の日, 勤労感謝の日, 天皇誕生日
	//
	// $dtjp3->holiDay	: false

	// 和暦、年号
	// 
	// $dtjp3->wareki	：平成23年11月01日
	// $dtjp3->nengo	：平成

	// ※注意
	// 直接はアクセス出来ないprivateなプロパティ
	//
	// $dtjp->timezone, $dtjp->timezone_type, $dtjp->date

	$brNl = '<br />' . PHP_EOL;

	echo '<pre>';
	var_dump($dtjp);
	echo '</pre>';

	/* publicな静的メソッド */

	// 特化：(DateTimeオブジェクトを元に)DateTimeJpオブジェクトを返す
	$dtjp4 = DateTimeJp::specialize(new DateTime());
	echo get_class($dtjp4) . $brNl;

	// 現在のマイクロ秒を取得する(浮動小数点 ver)
	echo DateTimeJp::getMicroSec() . $brNl;
	// 現在のマイクロ秒を取得する(文字列 ver)
	echo DateTimeJp::getMicroSec(false) . $brNl;

	// 現在月の最終日を取得
	echo DateTimeJp::lastDay() . $brNl;

	// 2012年02月の最終日を取得
	echo DateTimeJp::lastDay('2012/02/26') . $brNl;

	// 来月の最終日を取得
	// strtotime()に指定出来る文字列なら使用可
	echo DateTimeJp::lastDay('+1 month') . $brNl;

	// 閏年の判定
	echo var_export(DateTimeJp::isLeapYear(2011), true) . $brNl;
	echo var_export(DateTimeJp::isLeapYear(2012), true) . $brNl;

	// 春分の日を取得
	echo DateTimeJp::getSpringDay(2011) . $brNl;
	// 秋分の日を取得
	echo DateTimeJp::getFallDay(2011) . $brNl;

	// 日本の祝日か否か
	echo DateTimeJp::isHoliDay('2011/01/01') . $brNl;	// 元日
	echo DateTimeJp::isHoliDay('2011/01/10') . $brNl;	// 成人の日
	echo DateTimeJp::isHoliDay('2011/02/11') . $brNl;	// 建国記念の日
	echo DateTimeJp::isHoliDay('2011/03/21') . $brNl;	// 春分の日
	echo DateTimeJp::isHoliDay('2011/04/29') . $brNl;	// 昭和の日
	echo DateTimeJp::isHoliDay('2011/05/03') . $brNl;	// 憲法記念日
	echo DateTimeJp::isHoliDay('2011/05/04') . $brNl;	// みどりの日
	echo DateTimeJp::isHoliDay('2011/05/05') . $brNl;	// こどもの日
	echo DateTimeJp::isHoliDay('2011/07/18') . $brNl;	// 海の日
	echo DateTimeJp::isHoliDay('2011/09/19') . $brNl;	// 敬老の日
	echo DateTimeJp::isHoliDay('2011/09/23') . $brNl;	// 秋分の日
	echo DateTimeJp::isHoliDay('2011/10/10') . $brNl;	// 体育の日
	echo DateTimeJp::isHoliDay('2011/11/03') . $brNl;	// 文化の日
	echo DateTimeJp::isHoliDay('2011/11/23') . $brNl;	// 勤労感謝の日
	echo DateTimeJp::isHoliDay('2011/12/23') . $brNl;	// 天皇誕生日
	// なんでもない日
	echo var_export(DateTimeJp::isHoliDay('2011/12/29'), true) . $brNl;

	// 西暦を和暦に変換
	echo DateTimeJp::yearToWareki('2011-11-01') . $brNl;	// 平成26年11月09日
	echo DateTimeJp::yearToWareki2('2011-11-01') . $brNl;	// 平26年11月09日
	echo DateTimeJp::yearToWareki3('2011-11-01') . $brNl;	// 平26/11/09
	echo DateTimeJp::yearToWareki4('2011-11-01') . $brNl;	// H26/11/09
	echo DateTimeJp::yearToWareki5('2011-11-01') . $brNl;	// h26/11/09

	// 和暦を西暦に変換
	echo DateTimeJp::warekiToYear('平成23年11月01日') . $brNl;
	echo DateTimeJp::warekiToYear('平23年11月01日') . $brNl;
	echo DateTimeJp::warekiToYear('平23/11/01') . $brNl;
	echo DateTimeJp::warekiToYear('H23/11/01') . $brNl;
	echo DateTimeJp::warekiToYear('h23/11/01') . $brNl;

	// 西暦から年号を取得
	echo DateTimeJp::yearToWareki('1945-09-09', TRUE) . $brNl;
	echo DateTimeJp::yearToWareki2('1945-09-09', TRUE) . $brNl;
	echo DateTimeJp::yearToWareki3('1945-09-09', TRUE) . $brNl;
	echo DateTimeJp::yearToWareki4('1945-09-09', TRUE) . $brNl;
	echo DateTimeJp::yearToWareki5('1945-09-09', TRUE) . $brNl;

	/* public なメソッド */

	// 汎化：(DateTimeJpオブジェクトを元に)DateTimeオブジェクトを返す
	$dt = $dtjp3->generalize();
	echo get_class($dt) . $brNl;


	// DateTimeクラスを継承しているため、DateTimeクラスのメソッドを使うことが出来る
	// 参照 http://jp2.php.net/manual/ja/book.datetime.php

	// add()
	// format()
	// 特に意図していなかったが、メソッドチェーンも問題なく使えるようだ
	echo $dtjp->add(new DateInterval('P10D'))->format('Y/m/d') . $brNl;

	// getTimestamp()
	$date = new DateTimeJp();
	echo $date->getTimestamp() . $brNl;

	// 現在年月日日時秒を変更するようなメソッドを使った後でも、
	// プロパティの値は更新されているため気にする必要はない
	$dtjp5 = new DateTimeJp();
	echo $dtjp5->setTimestamp(0)->W3C . $brNl;

	// ※注意
	// 対応したのはオブジェクト指向型のみ
	// 手続き型は未対応。

} catch (Exception $e) {
	echo $e->getMessage();
}
?>