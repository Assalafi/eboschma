$(function () {
  //Date range picker - only initialize if daterangepicker is available
  if (typeof $.fn.daterangepicker === "function") {
    $("#reservation").daterangepicker()
  }
})
