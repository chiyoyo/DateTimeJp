<?php
/**
 * DateTimeJpクラス
 *
 * @version 1.2.0
 * @create 2011/08/11
 * @update 2014/11/09
 * @charset UTF-8
 * @author mamiya_shou
 * @licence MIT
 * @memo DateTimeオブジェクトを継承
 * @caution PHP 5.2以上必須、5.3以上推奨
 * @reference
 *	http://www.php.net/manual/ja/book.datetime.php
 */

// 祝日パターン
define('DEFINED_DAY',	0);	// パターン0：特定の日
define('N_DAY_OF_WEEK',	1);	// パターン1：n月の第m月曜
define('SPRING_FALL',	2);	// パターン2：春分・秋分の日

define('SPRING_POS',	3);	// 春分の日の配列位置
define('FALL_POS',		10);// 秋分の日の配列位置

// 文字エンコーディング
define('DATETIMEJP_ENCODING', 'UTF-8');

// タイムゾーン
define('TIMEZONE_JP',	'Asia/Tokyo');

// 年号用配列の初期化
$judgeNengos = array(
	'明治' => array('nengo' => '明治', 'start' => '1868-09-08', 'end' => '1912-07-29', 'baseYear' => 1867),
	'大正' => array('nengo' => '大正', 'start' => '1912-07-30', 'end' => '1926-12-24', 'baseYear' => 1911),
	'昭和' => array('nengo' => '昭和', 'start' => '1926-12-25', 'end' => '1989-01-07', 'baseYear' => 1925),
	'平成' => array('nengo' => '平成', 'start' => '1989-01-08', 'end' => '9999-12-31', 'baseYear' => 1988)
);
// 年号の数
$nengoCount = count($judgeNengos);
$judgeNengos['明'] =& $judgeNengos['明治'];
$judgeNengos['大'] =& $judgeNengos['大正'];
$judgeNengos['昭'] =& $judgeNengos['昭和'];
$judgeNengos['平'] =& $judgeNengos['平成'];
$judgeNengos['m'] =& $judgeNengos['明治'];
$judgeNengos['t'] =& $judgeNengos['大正'];
$judgeNengos['s'] =& $judgeNengos['昭和'];
$judgeNengos['h'] =& $judgeNengos['平成'];

$replaceYmds = array(
	1 => array('_YEAR_' => '年',	'_MONTH_' => '月',	'_DAY_' => '日'),	// 平成yy年mm月dd日 形式
	2 => array('_YEAR_' => '年',	'_MONTH_' => '月',	'_DAY_' => '日'),	// 平yy年mm月dd日 形式
	3 => array('_YEAR_' => '/',		'_MONTH_' => '/',	'_DAY_' => ''),		// 平yy/mm/dd 形式
	4 => array('_YEAR_' => '/',		'_MONTH_' => '/',	'_DAY_' => ''),		// Hyy/mm/dd 形式
);

// DateTimeJpクラス
class DateTimeJp extends DateTime
{
	private	$timezone;		// TimeZone クラス

	/**
	 * コンストラクタ
	 *
	 * @param $strTime strtotimeでUnixタイムスタンプに変換できる文字列
	 * 				  「2011/07/21」や「+1 day」など
	 */
	public function __construct($strTime = 'now')
	{
		// タイムゾーンを作成(日本)
		$this->timezone = new DateTimeZone(TIMEZONE_JP);

		// 引数無し(現在日付時刻)
		if ($strTime == 'now') {
			// マイクロ秒を取得する
			$strMicroTime = DateTimeJp::getMicroSec(false);
			// マイクロ秒を付加
			$strTime = date('Y-m-d H:i:s.' . $strMicroTime);
		}

		// 親クラスのコンストラクタを使う
		parent::__construct($strTime, $this->timezone);

		// 各プロパティ値を設定する
		$this->_setProperty();
	}

	/**
	 * 各プロパティに値を設定する
	 */
	private function _setProperty()
	{
		// フォーマット文字列
		$strFormat = 'dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU';

		for ($ii = 0, $max = strlen($strFormat); $ii < $max; $ii++) {
			// 1文字取得
			$chFormat = $strFormat[$ii];
			// プロパティに動的に設定
			$this->$chFormat = $this->format($chFormat);
		}

		// 定義済み定数でフォーマットした値もプロパティとして持つ
		$this->ATOM		= $this->format(DATE_ATOM);
		$this->COOKIE	= $this->format(DATE_COOKIE);
		$this->ISO8601	= $this->format(DATE_ISO8601);
		$this->RFC822	= $this->format(DATE_RFC822);
		$this->RFC850	= $this->format(DATE_RFC850);
		$this->RFC1036	= $this->format(DATE_RFC1036);
		$this->RFC1123	= $this->format(DATE_RFC1123);
		$this->RFC2822	= $this->format(DATE_RFC2822);
		$this->RFC3339	= $this->format(DATE_RFC3339);
		$this->RSS		= $this->format(DATE_RSS);
		$this->W3C		= $this->format(DATE_W3C);

		// and more...

		// マイクロ秒(実数型)
		$this->mSec		= (double)('0.' . $this->u);

		$ymd = $this->Y.$this->m.$this->d;
		// 祝日かどうか
		$this->holiDay	= self::isHoliDay($ymd);
		// 和暦年月日
		$this->wareki	= self::yearToWareki($ymd);
		// 和暦年月日
		$this->wareki2	= self::yearToWareki2($ymd);
		// 和暦年月日
		$this->wareki3	= self::yearToWareki3($ymd);
		// 和暦年月日
		$this->wareki4	= self::yearToWareki4($ymd);
		// 和暦年月日
		$this->wareki5	= self::yearToWareki5($ymd);
		// 年号
		$this->nengo	= self::yearToWareki($ymd, TRUE);
		// 年号2
		$this->nengo2	= self::yearToWareki2($ymd, TRUE);
		// 年号3(年号2と同じ)
		$this->nengo3	= $this->nengo2;
		// 年号4
		$this->nengo4	= self::yearToWareki4($ymd, TRUE);
		// 年号5
		$this->nengo5	= self::yearToWareki5($ymd, TRUE);
	}

	/**
	 * DateTime オブジェクトを返す(汎化)
	 *
	 * @return DateTime $objDt
	 */
	public function generalize()
	{
		// 日付文字列を生成
		$date = $this->format('Y-m-d H:i:s.u');

		// DateTime オブジェクトを返す
		return new DateTime($date, $this->timezone);
	}

	/**
	 * DateTimeJp オブジェクトを返す(特化)
	 *
	 * @return DateTimeJp オブジェクトを返す
	 */
	public static function specialize($objDt)
	{
		// 日付文字列を生成
		$date = $objDt->format('Y-m-d H:i:s.u');

		// DateTimeJp オブジェクトを返す
		return new DateTimeJp($date, new DateTimeZone(TIMEZONE_JP));
	}

	/**
	 * マイクロ秒を取得する
	 *
	 * @param boolean $bRetDbl	true  - 返り値を浮動小数点で返す
	 *							false - 返り値を文字列(小数点より後)で返す
	 * @return マイクロ秒(true : 最大浮動小数点以下第6位、false : 6桁)
	 */
	public static function getMicroSec($bRetDbl = true)
	{
		// タイムスタンプ、マイクロ秒に分割
		$aryMicroSec = explode(' ', microtime());

		// 浮動小数点フラグが立っていた場合
		if ($bRetDbl) {
			$ret = (double)$aryMicroSec[0];
		}
		else {
			// 小数点より後
			$ret = substr($aryMicroSec[0], 2, 6);
		}

		return $ret;
	}

	/**
	 * 月末日を取得する
	 *
	 * @param $strDt 日付文字列
	 * @return string 月末日
	 */
	public static function lastDay($strDt = 'now')
	{
		// タイムスタンプに変換
		$ts = strtotime($strDt);
		// 変換に失敗した場合
		if ($ts === FALSE) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'Can not be converted to a timestamp - $strDt : ' . $strDt,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		// 指定年月日の月初日
		$stTime = date('Y-m-01', $ts);
		// DateTime オブジェクトを生成
		$objDt = new DateTime($stTime);
		// 1ヶ月後
		$objDt->modify('+1 month');
		// 1日前
		$objDt->modify('-1 day');

		return $objDt->format('d');
	}

	/**
	 * 閏年かどうか
	 *
	 * @param int $year 年
	 * @return boolean true / false
	 */
	public static function isLeapYear($year)
	{
		// intキャスト
		$iYear = (int)$year;

		// キャストに失敗した場合
		if ($year !== 0 && $iYear === 0) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'Not an integer - $year : ' . $year,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		$bRet = false;
		// 閏年の場合
		if (($iYear % 4 == 0 && $iYear % 100 != 0) || $iYear % 400 == 0) {
			$bRet = true;
		}
		return $bRet;
	}

	/**
	 * 春分の日を取得する(1900～2099年まで)
	 * 
	 *
	 * @param $year 年
	 * @return integer 秋分の日
	 * @memo http://builder.japan.zdnet.com/blog/10502383/2008/07/05/entry_27011980/
	 */
	public static function getSpringDay($year)
	{
		// intキャスト
		$iYear = (int)$year;

		// キャストに失敗した場合
		if ($year !== 0 && $iYear === 0) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'Not an integer - $year : ' . $year,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		$springDay = 21.4471 + (0.242377 * ($year - 1900)) - floor(($year -1900) / 4.0);
		return (int)floor($springDay);
	}

	/**
	 * 秋分の日を取得する(1900～2099年まで)
	 *
	 * @param $year 年
	 * @return integer 秋分の日
	 * @memo http://builder.japan.zdnet.com/blog/10502383/2008/07/05/entry_27011980/
	 */
	public static function getFallDay($year)
	{
		// intキャスト
		$iYear = (int)$year;

		// キャストに失敗した場合
		if ($year !== 0 && $iYear === 0) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'Not an integer - $year : ' . $year,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		$fallDay = 23.8896 + (0.242032 * ($year - 1900)) - floor(($year -1900) / 4.0);
		return (int)floor($fallDay);
	}

	/**
	 * 祝日か否か
	 *
	 * @param $strDt 日付文字列
	 * @return string 祝日名 / false
	 */
	public static function isHoliDay($strDt)
	{
		// タイムスタンプに変換
		$ts = strtotime($strDt);
		// 変換に失敗した場合
		if ($ts === FALSE) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'Can not be converted to a timestamp - $strDt : ' . $strDt,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}
		// mm-dd形式
		$year = date('Y', $ts);
		$md = date('m-d', $ts);
		$aryDt = explode('-', $md);

		// 春分の日
		$springYmd = $year . '-03-' . DateTimeJp::getSpringDay($year);
		// 秋分の日
		$fallYmd   = $year . '-09-' . DateTimeJp::getFallDay($year);

		// 祝日配列
		$aryHoliDay = array(
			array('type' => DEFINED_DAY,	'date' => '01-01',		'name' => '元日'),
			array('type' => N_DAY_OF_WEEK,	'date' => '01-2',		'name' => '成人の日'),
			array('type' => DEFINED_DAY,	'date' => '02-11',		'name' => '建国記念の日'),
			array('type' => SPRING_FALL,	'date' => 'Not Used',	'name' => '春分の日'),
			array('type' => DEFINED_DAY,	'date' => '04-29',		'name' => '昭和の日'),
			array('type' => DEFINED_DAY,	'date' => '05-03',		'name' => '憲法記念日'),
			array('type' => DEFINED_DAY,	'date' => '05-04',		'name' => 'みどりの日'),
			array('type' => DEFINED_DAY,	'date' => '05-05',		'name' => 'こどもの日'),
			array('type' => N_DAY_OF_WEEK,	'date' => '07-3',		'name' => '海の日'),
			array('type' => N_DAY_OF_WEEK,	'date' => '09-3',		'name' => '敬老の日'),
			array('type' => SPRING_FALL,	'date' => 'Not Used',	'name' => '秋分の日'),
			array('type' => N_DAY_OF_WEEK,	'date' => '10-2',		'name' => '体育の日'),
			array('type' => DEFINED_DAY,	'date' => '11-03',		'name' => '文化の日'),
			array('type' => DEFINED_DAY,	'date' => '11-23',		'name' => '勤労感謝の日'),
			array('type' => DEFINED_DAY,	'date' => '12-23',		'name' => '天皇誕生日')
		);

		foreach ($aryHoliDay as $holiDay)
		{
			switch ($holiDay['type'])
			{
				case DEFINED_DAY:	// 特定の日
					if ($md == $holiDay['date']) {
						return $holiDay['name'];
					}
					break;

				case N_DAY_OF_WEEK:	// n月の第m週の月曜
					// 「-」で分割
					$aryMD = explode('-', $holiDay['date']);

					// 月が一致
					if ($aryDt[0] === $aryMD[0]) {
						// 月初日を生成
						$objDt = new DateTime($year . '-' . $aryDt[0] . '-01');

						// 月曜日の場合
						if ($objDt->format('w') == 1) {
							// 指定回数を1回減らす
							$aryMD[1] = (int)$aryMD[1];
							$aryMD[1]--;
						}
						// 指定回数分、次の月曜日に変更する
						for ($ii = 1; $ii <= (int)$aryMD[1]; $ii++) {
							$objDt->modify('next monday');
						}

						// 日が一致
						if ($aryDt[1] == $objDt->format('d')) {
							return $holiDay['name'];
						}
					}
					break;

				case SPRING_FALL:	// 春分・秋分の日
					$ymd = $year . '-' . $md;
					// 春分の日
					if ($springYmd == $ymd) {
						return $aryHoliDay[SPRING_POS]['name'];
					}
					// 秋分の日
					elseif ($fallYmd == $ymd) {
						return $aryHoliDay[FALL_POS]['name'];
					}
					break;
			}
		}

		return false;
	}

	/**
	 * 西暦年月日を和暦年月日に変換する(内部用)
	 *
	 * @param string $strYmd 西暦の年月日
	 * @param boolean $nengoFlg 年号フラグ(TRUEにすると年号のみを返す)
	 * @param boolean $gannenFlg 元年フラグ(TRUEにすると1年を元年にする)
	 * @return string 和暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 */
	private static function _yearToWareki($strYmd, $nengoFlg = FALSE, $gannenFlg = FALSE)
	{
		global $judgeNengos;

		if (($ts = strtotime($strYmd)) === FALSE) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'英文形式の日付を指定してください - $strYmd : ' . $strYmd,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		list($year, $month, $day) = explode('-', date('Y-m-d', $ts));

		$wareki = $nengo = $ret = NULL;
		foreach ($judgeNengos as $nengos) {
			if ($nengos['start'] <= $strYmd && $strYmd <= $nengos['end']) {
				$nengo  = $nengos['nengo'];
				$wareki = $year - $nengos['baseYear'];
				// 元年使用(元年フラグが立っている場合)
				if ($wareki === 1 && $gannenFlg === TRUE) {
					$wareki = '元';
				}

				if (! $nengoFlg) {
					$ret = "{$nengo}{$wareki}_YEAR_{$month}_MONTH_{$day}_DAY_";
				} else {
					$ret = $nengo;
				}
				break;
			}
		}
		if ($ret === NULL) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'サポートしている和暦の範囲外です - $strYmd : ' . $strYmd,
				__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		return $ret;
	}

	/**
	 * 西暦年月日を和暦年月日(平成yy年mm月dd日 形式)に変換する
	 *
	 * @param string $strYmd 西暦の年月日
	 * @param boolean $nengoFlg 年号フラグ(TRUEにすると年号のみを返す)
	 * @return string 和暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 */
	public static function yearToWareki($strYmd, $nengoFlg = FALSE)
	{
		global $replaceYmds;

		$ret = DateTimeJP::_yearToWareki($strYmd, $nengoFlg, TRUE);
		if ($nengoFlg === FALSE) {
			$idx = 1;
			$searches = array_keys($replaceYmds[$idx]);
			$replaces = array_values($replaceYmds[$idx]);
			$ret = str_replace($searches, $replaces, $ret);
		}
		return $ret;
	}

	/**
	 * 西暦年月日を和暦年月日(平yy年mm月dd日 形式)に変換する
	 *
	 * @param string $strYmd 西暦の年月日
	 * @param boolean $nengoFlg 年号フラグ(TRUEにすると年号のみを返す)
	 * @return string 和暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 */
	public static function yearToWareki2($strYmd, $nengoFlg = FALSE)
	{
		global $replaceYmds;
		global $judgeNengos;
		global $nengoCount;

		$ret = DateTimeJP::_yearToWareki($strYmd, FALSE, TRUE);

		$idx = 2;
		$searches = array_keys($replaceYmds[$idx]);
		$replaces = array_values($replaceYmds[$idx]);
		$ret = str_replace($searches, $replaces, $ret);

		$keys = array_keys($judgeNengos);
		for ($ii = ($idx - 1) * $nengoCount; $ii < $idx * $nengoCount; $ii++) {
			$key = $keys[$ii];
			if (mb_strpos($ret, $judgeNengos[$key]['nengo']) !== FALSE) {
				$ret = str_replace($judgeNengos[$key]['nengo'], $key, $ret);
				break;
			}
		}
		if ($nengoFlg === TRUE) {
			$ret = mb_substr($ret, 0, 1, DATETIMEJP_ENCODING);
		}
		return $ret;
	}

	/**
	 * 西暦年月日を和暦年月日(平yy/mm/dd 形式)に変換する
	 *
	 * @param string $strYmd 西暦の年月日
	 * @param boolean $nengoFlg 年号フラグ(TRUEにすると年号のみを返す)
	 * @return string 和暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 */
	public static function yearToWareki3($strYmd, $nengoFlg = FALSE)
	{
		global $replaceYmds;
		global $judgeNengos;
		global $nengoCount;

		$ret = DateTimeJP::_yearToWareki($strYmd, FALSE, TRUE);
		$idx = 3;
		$searches = array_keys($replaceYmds[$idx]);
		$replaces = array_values($replaceYmds[$idx]);
		$ret = str_replace($searches, $replaces, $ret);

		$keys = array_keys($judgeNengos);
		for ($ii = ($idx - 2) * $nengoCount; $ii < $idx * $nengoCount; $ii++) {
			$key = $keys[$ii];
			if (mb_strpos($ret, $judgeNengos[$key]['nengo']) !== FALSE) {
				$ret = str_replace($judgeNengos[$key]['nengo'], $key, $ret);
				break;
			}
		}
		if ($nengoFlg === TRUE) {
			$ret = mb_substr($ret, 0, 1, DATETIMEJP_ENCODING);
		}
		return $ret;
	}

	/**
	 * 西暦年月日を和暦年月日(Hyy/mm/dd 形式)に変換する
	 *
	 * @param string $strYmd 西暦の年月日
	 * @param boolean $nengoFlg 年号フラグ(TRUEにすると年号のみを返す)
	 * @return string 和暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 */
	public static function yearToWareki4($strYmd, $nengoFlg = FALSE)
	{
		global $replaceYmds;
		global $judgeNengos;
		global $nengoCount;

		$ret = DateTimeJP::_yearToWareki($strYmd, FALSE, TRUE);
		$idx = 3;
		$searches = array_keys($replaceYmds[$idx]);
		$replaces = array_values($replaceYmds[$idx]);
		$ret = str_replace($searches, $replaces, $ret);

		$keys = array_keys($judgeNengos);
		for ($ii = ($idx - 1) * $nengoCount; $ii < $idx * $nengoCount; $ii++) {
			$key = $keys[$ii];
			if (mb_strpos($ret, $judgeNengos[$key]['nengo']) !== FALSE) {
				$ret = str_replace($judgeNengos[$key]['nengo'], strtoupper($key), $ret);
				break;
			}
		}
		if ($nengoFlg === TRUE) {
			$ret = mb_substr($ret, 0, 1, DATETIMEJP_ENCODING);
		}
		return $ret;
	}

	/**
	 * 西暦年月日を和暦年月日(hyy/mm/dd 形式)に変換する
	 *
	 * @param string $strYmd 西暦の年月日
	 * @param boolean $nengoFlg 年号フラグ(TRUEにすると年号のみを返す)
	 * @return string 和暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 */
	public static function yearToWareki5($strYmd, $nengoFlg = FALSE)
	{
		global $replaceYmds;
		global $judgeNengos;
		global $nengoCount;

		$ret = DateTimeJP::_yearToWareki($strYmd, FALSE, TRUE);
		$idx = 3;
		$searches = array_keys($replaceYmds[$idx]);
		$replaces = array_values($replaceYmds[$idx]);
		$ret = str_replace($searches, $replaces, $ret);

		$keys = array_keys($judgeNengos);
		for ($ii = ($idx - 1) * $nengoCount; $ii < $idx * $nengoCount; $ii++) {
			$key = $keys[$ii];
			if (mb_strpos($ret, $judgeNengos[$key]['nengo']) !== FALSE) {
				$ret = str_replace($judgeNengos[$key]['nengo'], $key, $ret);
				break;
			}
		}
		if ($nengoFlg === TRUE) {
			$ret = mb_substr($ret, 0, 1, DATETIMEJP_ENCODING);
		}
		return $ret;
	}

	/**
	 * 和暦年月日を西暦年月日に変換する
	 *
	 * @param string $strYmd 和暦の年月日
	 * @param string $encoding 文字エンコーディング
	 * @return string 西暦年月日(OK) / NULL(NG)
	 * @exception Exception
	 * @cation 年号、年、月、日の順になっている必要がある
	 */
	public static function warekiToYear($strYmd, $encoding = NULL)
	{
		global $judgeNengos;

		// エンコーディング指定なし
		if ($encoding === NULL) {
			// 内部エンコーディングを使用
			$encoding = mb_internal_encoding();
		}

		// 元年を1年に変換
		$strYmd = str_replace('元', '1', $strYmd);

		// 空白文字類を半角スペースに変換
		$strYmd = preg_replace('/\s/is', ' ', $strYmd);
		if ($strYmd === FALSE) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'空白文字類の置換に失敗しました。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		// a：「全角」英数字を「半角」に変換
		// s：「全角」スペースを「半角」に変換
		$strYmd = mb_convert_kana($strYmd, 'as', $encoding);

		// 大文字を小文字に変換
		$strYmd = strtolower($strYmd);

		// 年号部分が存在しない場合
		$matches = NULL;
		if (! preg_match('/明|大|昭|平|明治|大正|昭和|平成|m|t|s|h/is', $strYmd, $matches)) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'未定義の年号です。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}
		// 年号
		$nengo = $matches[0];

		// 年号部分を削除
		$strYmd = str_replace($nengo, '', $strYmd);
		// 数字以外を半角スペースに変換
		$strYmd = preg_replace('/[^\d]+/', ' ', $strYmd);
		if ($strYmd === FALSE) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'数字以外の置換に失敗しました。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		list($wareki, $month, $day) = sscanf($strYmd, '%s %s %s');
		// 2桁0埋め
		$month = sprintf('%02d', $month);
		$day = sprintf('%02d', $day);

		// 和暦は1,2桁
		if (! preg_match('/^\d{1,2}$/', $wareki)) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'不正な和歴です。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		if ($wareki <= 0) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'年は1以上を指定してください。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		$judgeNengo = $judgeNengos[$nengo];
		// 西暦変換
		$year = $wareki + $judgeNengo['baseYear'];

		$ymd = "$year-$month-$day";
		$bMatch = FALSE;
		foreach ($judgeNengos as $nengos) {
			if ($nengos['start'] <= $ymd && $ymd <= $nengos['end']) {
				$bMatch = TRUE;
				break;
			}
		}
		if (! $bMatch) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'範囲外の年月日です。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}
		// 日付の妥当性を検証
		if (! checkdate($month, $day, $year)) {
			// 例外メッセージを取得
			$msg = getExceptionMsg(
				'不正な年月日です。',	__FILE__, __LINE__
			);
			throw new Exception($msg);
		}

		return $ymd;
	}

	// 以下、自オブジェクトを変更するメソッド
	// 親クラスのメソッドをコールした後に、各プロパティ値を再設定する

	/**
	 * 指定した書式でフォーマットした新しい DateTimeJp オブジェクトを返す
	 *
	 * @param string $format 書式を文字列で渡します
	 * @param string $time 時刻を表す文字列
	 * @param DateTimeZone $timezone 指定したいタイムゾーンを表す DateTimeZone オブジェクト
	 * @return DateTimeJpオブジェクト
	 * @memo DateTime::createFromFormat のオーバーロード
	 *		 このメソッドは値を変えているわけではないのでプロパティの再設定不要
	 */
	public static function createFromFormat($format, $time, $timezone = NULL)
	{
		// タイムゾーン有り
		if ($timezone) {
			// 親クラスのメソッドをコール
			$objDt = DateTime::createFromFormat($format, $time, $timezone);
		}
		// 無し
		else {
			// 親クラスのメソッドをコール
			$objDt = DateTime::createFromFormat($format, $time);
		}
		// 親クラスのメソッドの返り値を特化(DateTimeJpに変換)
		return DateTimeJp::specialize($objDt);
	}

	/**
	 * 日付を設定する
	 *
	 * @param int $year  その日付の年
	 * @param int $month その日付の月
	 * @param int $day   その日付の日
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::setDate のオーバーロード
	 */
	public function setDate($year, $month, $day)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::setDate($year, $month, $day);

		if ($objRet) {
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}

	/**
	 * ISO 日付を設定する
	 *
	 * @param int $year その日付の年。
	 * @param int $week その日付の週。
	 * @param int $day  週の最初の日からのオフセット。
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::setISODate のオーバーロード
	 */
	public function setISODate($year, $week, $day = 1)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::setISODate($year, $week, $day);

		if ($objRet) {
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}

	/**
	 * 時刻を設定する
	 *
	 * @param int $hour   その時刻の時。
	 * @param int $minute その時刻の分。
	 * @param int $second その時刻の秒。
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::setTime のオーバーロード
	 */
	public function setTime($hour, $minute, $second = 0)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::setTime($hour, $minute, $second);

		if ($objRet) {
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}

	/**
	 * 時刻を設定する
	 *
	 * @param DateTimeZone $timezone 指定したいタイムゾーンを表す DateTimeZone オブジェクト。
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::setTimezone のオーバーロード
	 */
	public function setTimezone($timezone)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::setTimezone($timezone);

		if ($objRet) {
			$this->timezone = $timezone;
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}

	/**
	 * 年月日時分秒の値を DateTime オブジェクトに加える
	 *
	 * @param DateInterval $interval DateInterval オブジェクト。
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::add のオーバーロード
	 */
	public function add($interval)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::add($interval);

		if ($objRet) {
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}

	/**
	 * 年月日時分秒の値を DateTime オブジェクトから引く 
	 *
	 * @param DateInterval $interval DateInterval オブジェクト。
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::sub のオーバーロード
	 */
	public function sub($interval)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::sub($interval);

		if ($objRet) {
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}

	/**
	 * Unix タイムスタンプを用いて日付と時刻を設定する
	 *
	 * @param int Unixタイムスタンプ。
	 * @return DateTimeJp / FALSE
	 * @memo DateTime::setTimestamp のオーバーロード
	 */
	public function setTimestamp($unixtimestamp)
	{
		// 親クラスのメソッドをコール
		$objRet = parent::setTimestamp($unixtimestamp);

		if ($objRet) {
			// 各プロパティに値を再設定する
			$this->_setProperty();
		}

		return $objRet;
	}
}

/**
 * 例外メッセージを取得する
 *
 * @param string $msg 例外メッセージ
 * @param string $file ファイルパス(__FILE__)
 * @param int $line 行番号(__LINE__)
 * @return 例外メッセージ
 *
 */
function getExceptionMsg($msg, $file, $line)
{
	return '<b>Fatal error</b> : ' . $msg .
		   ' <b>' . $file . '</b> on line <b>' . $line . '</b>';
}
?>