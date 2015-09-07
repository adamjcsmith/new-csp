// JavaScript Document

function getBaseURL () {
   return location.protocol + '//' + location.hostname + 
      (location.port && ':' + location.port) + '/';
}

(function() {
    tinymce.create('tinymce.plugins.vecb_button3', {
        init : function(ed, url) {
            ed.addButton('vecb_button3', {
                title : 'Circular Image',image : url+'/icons/circle-ok.png',onclick : function() {
                     ed.selection.setContent('<div class="circle">' + ed.selection.getContent() + '</div>');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('vecb_button3', tinymce.plugins.vecb_button3);
})();