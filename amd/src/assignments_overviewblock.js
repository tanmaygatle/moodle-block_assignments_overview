define(['jquery', 'core/tree'], function($, Tree) {
    return {
        init: function() {
          new Tree("#mycoursestree"); // css/jquery selector of the tree container
        }
    };
});
