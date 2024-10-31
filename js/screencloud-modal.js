var selectedPostId = 0;

document.addEventListener("DOMContentLoaded", function () {
  window.openScreenCloudModal = function (postId) {
    selectedPostId = postId;
    jQuery("#screencloud-modal").dialog({
      modal: true,
      width: 400,
    });
  };

  document
    .getElementById("shareWithScreenCloud")
    ?.addEventListener("click", function () {
      var configIndex = jQuery("#screencloud-connections-dropdown").val();
      sendToScreenCloud(selectedPostId, configIndex);
    });

  function sendToScreenCloud(postId, configIndex) {
    jQuery("#config-selection").hide();
    jQuery("#loading-indicator").show();
    jQuery.ajax({
      url: screencloudAjax.ajax_url,
      type: "POST",
      data: {
        action: "screencloud_send_post",
        post_id: postId,
        config_index: configIndex,
        _wpnonce: screencloudAjax.nonce,
      },
      success: function (response) {
        console.log(response);
        alert(
          "Content successfully sent to ScreenCloud! This will be visible on screen within a few minutes."
        );
      },
      error: function (error) {
        console.error(error);
        alert(
          `Failed to share the post! ${
            error.responseJSON && typeof error.responseJSON.data === "string"
              ? error.responseJSON.data
              : error.responseJSON.data.message
              ? error.responseJSON.data.message
              : ""
          }. For more details, check the browser console.`
        );
      },
      complete: function () {
        jQuery("#loading-indicator").hide();
        jQuery("#config-selection").show();
        jQuery("#screencloud-modal").dialog("close");
      },
    });
  }
});
