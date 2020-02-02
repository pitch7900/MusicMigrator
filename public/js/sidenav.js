var SideNavBar = function () {
    var add_goback_menu = function () {
        $(".nested").prepend("<a class='gobacktoparent'> Go Back</a>");
        $(".nested").hide();
        $(".nested").parent("li").addClass("parentfolder");
    };
    var changelinkstyle = function () {
        $(".sidenavbar a").addClass($(".sidenavbar").attr("linkstyle"));
    };

   
    var listener_on_parent_folder = function () {
        $('body').on('click', '.parentfolder', function () {
            console.log("Click on open Folder detected");

            $(this).siblings().hide();
            $(this).removeClass("parentfolder");

            $(this).children(".nested").show();

        });
    };
    var listener_on_goback_folder = function () {
        $('body').on('click', '.gobacktoparent', function () {
            $(this).parents("li").addClass("parentfolder");
            console.log("Click on GoBack detected");
            $(this).parents("li").siblings().show();//.removeClass("collapse");
            $(this).parents("li").children(".nested").hide();//.addClass("collapse");
        });
    };
//    var listener_on_click_list = function () {
//        $('body').on('click', 'ul.sidenavbarlist li', function () {
//
//            $(this).parent().children().removeClass('selected1');
//            $(this).parent().children().addClass('notselected1');
//            $(this).addClass("selected1").removeClass("notselected1");
//
//        });
//    };

    return {
        init: function () {
            add_goback_menu();
            changelinkstyle();
            listener_on_parent_folder();
            listener_on_goback_folder();
//            listener_on_click_list();
        }
    };
}();
