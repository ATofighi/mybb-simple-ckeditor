CKEDITOR.plugins.add( 'mybbfixquote', {
    init: function( editor ) {
        editor.on('instanceReady', function(){
            editor.setData(editor.getData()+"‌");
        });
    }
});
