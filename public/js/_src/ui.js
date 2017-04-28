iCMS.define("ui",{
        dialog: function(opts) {
            var dialog = iCMS.require("ui.dialog");
            return dialog(opts);
        },
        success: function(msg, callback) {
            this.alert(msg, true, callback);
        },
        alert: function(msg, ok, callback) {
            var dialog = iCMS.require("ui.dialog");
                var opts = ok ? {
                    label: 'success',
                    icon: 'check'
                } : {
                    label: 'warning',
                    icon: 'warning'
                }
                opts.id      = 'iCMS-DIALOG-ALERT';
                opts.skin    = 'iCMS_dialog_alert'
                opts.content = msg;
                opts.time    = 3000;
                opts.modal   = true;
                dialog(opts, callback);
            // });
        },
        /**
         * [seccode 验证码刷新]
         * @param  {[type]} a [验证码]
         * @param  {[type]} b [容器]
         */
        seccode:function(a, b) {
            var API = iCMS.require("api"),
            a = a||'.seccode-img',
            b = b||'body';
            $(a, b).attr('src', API.url('public', '&do=seccode&') + Math.random());
        }
});
