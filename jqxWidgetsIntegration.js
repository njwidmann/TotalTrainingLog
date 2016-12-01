/**
 * Created by Nick on 9/28/2014.
 */
$(document).ready(function() {

    $(".jqxMenu").jqxMenu({ width: '96.5%', height: '37px', autoOpen: false, autoCloseOnMouseLeave: false, showTopLevelArrows: true});

    $(".jqxMenu").css('visibility', 'visible');

    //$(".jqxMenuButton").jqxButton({ width: 60, height: 18});

   // $("#jqxSelectMenu").jqxDropDownList({ width: 300, height: 25 });


    //loading dialog
    (function($) {
        $.widget("artistan.loading", $.ui.dialog, {
            options: {
                // your options
                spinnerClassSuffix: 'spinner',
                spinnerHtml: 'Loading',// allow for spans with callback for timeout...
                maxHeight: false,
                maxWidth: false,
                minHeight: 80,
                minWidth: 220,
                height: 80,
                width: 220,
                modal: true
            },

            _create: function() {
                $.ui.dialog.prototype._create.apply(this);
                // constructor
                $(this.uiDialog).children('*').hide();
                var self = this,
                    options = self.options;
                self.uiDialogSpinner = $('.ui-dialog-content',self.uiDialog)
                    .html(options.spinnerHtml)
                    .addClass('ui-dialog-'+options.spinnerClassSuffix);
            },
            _setOption: function(key, value) {
                var original = value;
                $.ui.dialog.prototype._setOption.apply(this, arguments);
                // process the setting of options
                var self = this;

                switch (key) {
                    case "innerHeight":
                        // remove old class and add the new one.
                        self.uiDialogSpinner.height(value);
                        break;
                    case "spinnerClassSuffix":
                        // remove old class and add the new one.
                        self.uiDialogSpinner.removeClass('ui-dialog-'+original).addClass('ui-dialog-'+value);
                        break;
                    case "spinnerHtml":
                        // convert whatever was passed in to a string, for html() to not throw up
                        self.uiDialogSpinner.html("" + (value || '&#160;'));
                        break;
                }
            },
            _size: function() {
                $.ui.dialog.prototype._size.apply(this, arguments);
            },
            // other methods
            loadStart: function(newHtml){
                if(typeof(newHtml)!='undefined'){
                    this._setOption('spinnerHtml',newHtml);
                }
                this.open();
            },
            loadStop: function(){
                this._setOption('spinnerHtml',this.options.spinnerHtml);
                this.close();
            }
        });
    })(jQuery);

//    $("#loading_dialog").loading();
// uncomment one below for more details...
// $("#loading_dialog").loading("loadStart");
// $("#loading_dialog").loading("loadStart","Updating");
// $("#loading_dialog").loading("loadStop");


});