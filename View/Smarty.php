<?php
require_once SMARTY_REQUIRE_PATH;

/**
 * Smarty用
 */
class App_View_Smarty extends Zend_View_Abstract
{

    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty;

    /**
     * 文字列のエスケープに使う関数
     * @var string
     */
    protected $_escape  =   'htmlentities';

    /**
     * キャッシュID
     * @var <type>
     */
    protected $_cache_id = null;

    /**
     * キャッシュ対象のファイル一覧
     * @var <type>
     */
    protected $_cacheTargetList = null;

    /**
     * デバッグモード
     * @var <type>
     */
    protected $_debug_mode = false;

    /**
     * ロックファイル
     * @var <type>
     */
    protected $_lock_file = '';

    /**
     * コンストラクタ
     *
     * @param Smarty $smarty
     * @return void
     *
     */
    //public function __construct( Smarty $smarty = null )
    public function __construct($tmplPath = null, $extraParams = array())
    {
        // オブジェクト生成
        $this->_smarty = new Smarty();

        // テンプレートパス設定
        if (null !== $tmplPath) {
            $this->setScriptPath($tmplPath);
        }

        // 設定情報をメンバ変数へ
        foreach ($extraParams as $key => $value) {

            //----------------------------------------
            // 設定情報読み込み
            //----------------------------------------
            // array
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    // 出力時の関数指定
                    if ($key === "outputfilter") {
                        $this->_smarty->autoload_filters['output'][] = $value2;
                    }
                    // smartyに登録する関数
                    elseif ($key === "register_function") {
                        $func = explode('/', $value2);
                        if (count($func) > 1) {
                            $registFunc = array($func[0], $func[1]);
                        }
                        else {
                            $registFunc = $func[0];
                        }
                        $this->_smarty->register_function($key2, $registFunc);
                    }
                    else {
                        $this->_smarty->{$key}[] = $value2;
                    }
                }
            }
            // string
            else {
                // smarty のメンバ変数にはないので。。。
                if ($key === "cache_id") {
                    $this->_cache_id = $value;
                }
                elseif ($key === "debug_mode") {
                    $this->_debug_mode = $value;
                }
                // キャッシュ対象情報読み込み
                elseif ($key === "cache_target") {
                    $this->setCacheFile($value);
                }
                else {
                    $this->_smarty->$key = $value;
                }
            }
        }

        //----------------------------------------
        // ディレクトリチェック
        //----------------------------------------
        if (!is_dir($this->_smarty->cache_dir) || !is_writable($this->_smarty->cache_dir)) {
            throw new Zend_Exception('smartyキャッシュディレクトリに書き込みできません。' . $this->_smarty->cache_dir);
        }
        if (!is_dir($this->_smarty->compile_dir) || !is_writable($this->_smarty->compile_dir)) {
            throw new Zend_Exception('smartyコンパイルディレクトリに書き込みできません。' . $this->_smarty->compile_dir);
        }

        //----------------------------------------
        // キャッシュ対象情報読み込み
        //----------------------------------------
        // ロックファイル作成
        $this->_lock_file = $this->_smarty->cache_dir . $_SERVER['HTTP_HOST'] . '/cache.lock';

        // Zend_Db_Table_Rowsetをコンバートしてしますため一旦保留
        //$this->_smarty->default_modifiers = 'escape : "html"';
        $this->_smarty->php_handling      = SMARTY_PHP_PASSTHRU;

        // LayOutは使わない
        //$this->assign('layout', $this->layout());
        $this->assign('helper', $this);
    }

    /**
     * テンプレートエンジンオブジェクトを返します
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }

    /**
     * テンプレートへのパスを設定します
     *
     * @param string $path パスとして設定するディレクトリ
     * @return void
     */
    public function setScriptPath($path)
    {
        if (is_readable($path)) {
            $this->_smarty->template_dir = $path;
            return;
        }

        throw new Exception("無効なパスが指定されました : '$path'");
    }

    /**
     * スクリプトパスの追加
     *
     * @param string $name
     */
    public function addScriptPath($name)
    {
        $this->setScriptPath($name);
    }

    /**
     * 現在のテンプレートディレクトリを取得します
     *
     * @return string
     */
    public function getScriptPaths()
    {
        return array($this->_smarty->template_dir);
    }

    /**
     * Return full path to a view script specified by $name
     *
     * @param  string $name
     * @return false|string False if script not found
     * @throws Zend_View_Exception if no script directory set
     */
    public function getScriptPath($name="")
    {
        // ファイルが無くても処理をすすめるようにする。
        return $this->_smarty->template_dir;
        /*
        try {
            $path = $this->_script($name);
            return $path;
        }
        catch (Zend_View_Exception $e) {
            if (strstr($e->getMessage(), 'no view script directory set')) {
                throw $e;
            }

            return false;
        }
        */
    }

    /**
     * setScriptPath へのエイリアス
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function setBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * addScriptPath へのエイリアス
     *
     * @param string $path
     * @param string $prefix Unused
     * @return void
     */
    public function addBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * 変数をテンプレートに代入します
     *
     * @param string $key 変数名
     * @param mixed $val 変数の値
     * @return void
     */
    public function __set($key, $val)
    {
        $this->_smarty->assign($key, $val);
    }

    /**
     * 代入された変数を取得します
     *
     * @param string $key 変数名
     * @return mixed 変数の値
     */
    public function __get($key)
    {
        return $this->_smarty->get_template_vars($key);
    }

    /**
     * empty() や isset() のテストが動作するようにします
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return (null !== $this->_smarty->get_template_vars($key));
    }

    /**
     * オブジェクトのプロパティに対して unset() が動作するようにします
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->_smarty->clear_assign($key);
    }

    /**
     * 変数をテンプレートに代入します
     *
     * 指定したキーを指定した値に設定します。あるいは、
     * キー => 値 形式の配列で一括設定します
     *
     * @see __set()
     * @param string|array $spec 使用する代入方式 (キー、あるいは キー => 値 の配列)
     * @param mixed $value (オプション) 名前を指定して代入する場合は、ここで値を指定します
     * @return void
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }

        $this->_smarty->assign($spec, $value);
    }

    /**
     * テンプレートを処理し、結果を出力します
     *
     * @param string $name 処理するテンプレート
     * @return string 出力結果
     */
    public function render($name)
    {
        return $this->_smarty->fetch($name, $this->_cache_id);
    }

    /**
     * プレフィルターのセット
     *
     * @param string $filter
     * @param string $key
     */
    public function setPreFilter( $filter, $key )
    {
        $this->_smarty->autoload_filters['pre'][$key] = $filter;
    }

    /**
     * ポストフィルターのセット
     *
     * @param string $filter
     * @param string $key
     */
    public function setPostFilter( $filter, $key )
    {
        $this->_smarty->autoload_filters['post'][$key] = $filter;
    }

    /**
     * アウトプットフィルターのセット
     * @param string $filter
     * @param string $key
     */
    public function setOutputFilter( $filter, $key )
    {
        $this->_smarty->autoload_filters['output'][$key] = $filter;
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_encoding} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
        return call_user_func($this->_escape, $var, ENT_COMPAT );
        }

        return call_user_func($this->_escape, $var);
    }

    /**
     * Accesses a helper object from within a script.
     *
     * If the helper class has a 'view' property, sets it with the current view
     * object.
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        // call the helper method
        return call_user_func_array(
            array($helper, $name),
            $args
        );
    }

    /**
     * Includes the view script in a scope with only public $this variables.
     * @param string The view script to execute.
     */
    protected function _run()
    {
        include func_get_arg(0);
    }

    /**
     * 代入済みのすべての変数を削除します
     *
     * Zend_View に {@link assign()} やプロパティ
     * ({@link __get()}/{@link __set()}) で代入された変数をすべて削除します
     *
     * @return void
     */
    public function clearVars()
    {
        //$this->_smarty->clear_all_assign();
    }


    //--------------------------------------------------------------------------
    // キャッシュ関係の処理
    //--------------------------------------------------------------------------
    /**
     * キャッシュ対象情報取得処理
     *
     * @param <type> $file
     */
    public function setCacheFile($file)
    {
        if (!file_exists($file)) {
            throw new Zend_Exception('no cache taget file >> ' . $file);
        }

        $this->_cacheTargetList = new Zend_Config_Ini( $file , 'cacheTarget' );
    }

    /**
     * キャッシュ有効化
     *
     *      キャッシュは「cache_id」を強制的に以下のフォーマットで対応する
     *      [format]
     *          {FQDN}|{module}|{controller}|{action}
     *
     *      [cache directory]
     *          {FQDN}/{module}/{controller}/{action}
     *
     * @param <type> $module
     * @param <type> $controller
     * @param <type> $action
     * @return <type>
     */
    public function ableCache($module, $controller, $action)
    {
        // デバッグ
        if ($this->_debug_mode) {
            $this->_smarty->caching = false;
            return false;
        }

        // デフォルトのモジュールに指定
        if ($module !== "") {
            $module = "default";
        }

        // リスト存在チェック
        if (!is_object($this->_cacheTargetList)) {
            $this->_smarty->caching = false;
            return false;
        }

        // キャッシュ対象外
        if (    !is_object($this->_cacheTargetList->target->$module)
             || !is_object($this->_cacheTargetList->target->$module->$controller)
            ) {
            $this->_smarty->caching = false;
            return false;
        }

        // キャッシュ対象外
        if (!$this->_cacheTargetList->target->$module->$controller->$action) {
            Zend_Registry::get('logger')->log("=====< init >=====" . "not cache target > " . $module . "/" . $controller . "/" . $action, Zend_Log::INFO);
            $this->_smarty->caching = false;
            return false;
        }
        Zend_Registry::get('logger')->log("=====< init >=====" . "cache target > " . $module . "/" . $controller . "/" . $action, Zend_Log::INFO);

        // キャッシュを有効にする
        $this->_smarty->caching = true;

        // キャッシュIDの設定
        $this->_cache_id = $_SERVER['HTTP_HOST'] . '|' . $module . '|' . $controller . '|' . $action . '|' . md5($_SERVER['REQUEST_URI']);

        //--------------------------------------------------
        // ロック関連
        //--------------------------------------------------
        // ロックファイルがあればキャッシュしない
        if (file_exists($this->_lock_file)) {
            $fileInfo = stat($this->_lock_file);
            if ((time() - $fileInfo['mtime']) > 10) {
                unlink($this->_lock_file);
            }
            // キャッシュを無効にする
            $this->_smarty->caching = false;
            return false;
        }

        return true;
    }

    /**
     * キャッシュ削除時ロック処理
     *
     * @param <type> $module
     * @param <type> $controller
     * @param <type> $action
     * @return <type>
     */
    public function lockForCache($module, $controller, $action)
    {
        // デバッグ
        if ($this->_debug_mode) {
            return ;
        }

        // ロック対象
        if ($this->_cacheTargetList->lock->$module->$controller->$action) {
            // ロックファイルがあれば削除
            $lockFile = $this->_lock_file;
            if (file_exists($lockFile)) {
                unlink($lockFile);
            }
            // ロックファイルがなければ時間を格納して作成
            else {
                touch($lockFile);
            }
        }
    }

    /**
     * キャッシュ削除時ロック解除処理
     * @return <type>
     */
    public function unlockForCache()
    {
        // デバッグ
        if ($this->_debug_mode) {
            return ;
        }

        if (file_exists($this->_lock_file)) {
            unlink($this->_lock_file);
        }
    }

    /**
     * キャッシュクリア
     *
     * @param <type> $targetsData
     */
    public function clearCache($module, $controller, $action, $option)
    {
        // デバッグ
        if ($this->_debug_mode) {
            return ;
        }

        // ロックをかける処理かどうかのチェック
        if (isset($this->_cacheTargetList->lock->$module->$controller->$action)) {
            $target = $this->_cacheTargetList->lock->$module->$controller->$action;
            $targets = explode(',', $target);
        }
        else {
            return;
        }

        // 指定された分キャッシュデータを削除
        foreach ($targets as $target) {

            // オプションあり？
            if ($option != '') {
                $target .= '_' . $option;
            }

            // ターゲットあり
            if (isset($this->_cacheTargetList->delete->$target)) {
                // 配列化
                $list = $this->_cacheTargetList->delete->$target->toArray();

                // 空であればNULLを格納
                if (empty($list['file'])) {
                    $list['file'] = null;
                }

                // 配列に統一
                if (!is_array($list['cache_id'])) {
                    $cacheList = array($list['cache_id']);
                }
                else {
                    $cacheList = $list['cache_id'];
                }

                foreach ($cacheList as $cache_id) {
                    // キャッシュ
                    $this->_smarty->clear_cache($list['file'], $_SERVER['HTTP_HOST'] . $cache_id);
                }
            }
        }

        // ロック解除
        $this->unlockForCache();
    }

    /**
     * キャッシュテンプレート表示処理
     *
     * @param <type> $name
     */
    public function display($name)
    {
        // キャッシュされていれば、それを表示する
        if ($this->_smarty->is_cached($name, $this->_cache_id)) {
            $this->_smarty->display($name, $this->_cache_id);
            exit ;
        }
    }
}
