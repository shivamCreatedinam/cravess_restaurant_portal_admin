/*
This is common functions JS Created By Vijay Amule
*/

$(document).ready(function () {
    $("#category_id").on("change", function () {
        let cat_id = $(this).val();
        $.ajax({
            type: "post",
            url: getSubCategoryUrl,
            data: {
                category_id: cat_id,
            },
            success: function (response) {
                $("#sub_category_id").html(response.data.options);
            },
        });
    });

    $("#sub_category_id").on("change", function () {
        let cat_id = $('#category_id').find(":selected").val();
        let sub_cat_id = $(this).val();
        $.ajax({
            type: "post",
            url: getChildCategoryUrl,
            data: {
                category_id: cat_id,
                sub_category_id: sub_cat_id,
            },
            success: function (response) {
                $("#child_category_id").html(response.data.options);
            },
        });
    });
});
