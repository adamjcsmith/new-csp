// JavaScript Document

function getBaseURL () {
   return location.protocol + '//' + location.hostname + 
      (location.port && ':' + location.port) + '/';
}

(function() {
    tinymce.create('tinymce.plugins.vecb_button4', {
        init : function(ed, url) {
            ed.addButton('vecb_button4', {
                title : 'Panel',image : url+'/icons/framed.png',onclick : function() {
                     ed.selection.setContent('<div class="panel">' + ed.selection.getContent() + '</div>');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('vecb_button4', tinymce.plugins.vecb_button4);
})();