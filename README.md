# Usage

;--------------------
;smarty
;--------------------
resources.viewsmarty.template_dir    = TEMPLATE_DIR ""
resources.viewsmarty.use_sub_dirs   = true

; compile
resources.viewsmarty.compile_dir     = COMPILE_DIR ""
resources.viewsmarty.compile_id      = SMARTY_COMPILE_ID

; cache
resources.viewsmarty.debug_mode      = true
resources.viewsmarty.caching         = false

resources.viewsmarty.cache_target    = CONFIG_DIR "cache_target.ini"
resources.viewsmarty.cache_dir       = CACHE_DIR ""
resources.viewsmarty.cache_id        = SMARTY_CACHE_ID
resources.viewsmarty.cache_lifetime  = 3600
resources.viewsmarty.compile_check   = true

resources.viewsmarty.left_delimiter  = "<%"
resources.viewsmarty.right_delimiter = "%>"
resources.viewsmarty.plugins_dir.0   = "plugins"
resources.viewsmarty.plugins_dir.1   = SMARTY_PLUGINS_DIR_CSTM ""
resources.viewsmarty.suffix          = "html"
;resources.viewsmarty.default_modifiers.0 = "escape"
;resources.viewsmarty.default_modifiers.1 = "nl2br"
;resources.viewsmarty.outputfilter.0    = "convertEucjp"
;resources.viewsmarty.outputfilter.0    = "convertSjis"
;resources.viewsmarty.register_function.get_img_tag = "Model_Image/smarty_getImgTag"
