document.addEventListener("DOMContentLoaded", function () {
    const steps = document.querySelectorAll("#single-page-step-form .step");
    let currentStep = 0;

    function showStep(index) {
        steps.forEach((step, i) => step.classList.toggle("active-step", i === index));
    }

    document.querySelectorAll("#single-page-step-form .next").forEach(btn =>
        btn.addEventListener("click", () => {
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        })
    );

    document.querySelectorAll("#single-page-step-form .prev").forEach(btn =>
        btn.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        })
    );
});

jQuery(document).ready(function($) {

    let $steps = $(".step");
    let currentStep = 0;

    function showStep(n) {
        $steps.removeClass("active-step");
        $steps.eq(n).addClass("active-step");
    }

    // Next button
    $(".step-btn.next").on("click", function() {
        if (currentStep < $steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        }
    });

    // Prev button
    $(".step-btn.prev").on("click", function() {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // Submit
    $("#single-page-step-form").on("submit", function(e) {
        e.preventDefault();

        let $condition = $("input[name='phone_condition']:checked");
        let $carrier   = $("input[name='phone_carrier']:checked");
        let $storage   = $("input[name='phone_storage']:checked");
        let accessories = $("input[name='accessories[]']:checked").map(function(){ 
            return $(this).val(); 
        }).get();

        let totalPrice = 0;
        if ($condition.length) totalPrice += parseFloat($condition.data("price") || 0);
        if ($carrier.length)   totalPrice += parseFloat($carrier.data("price") || 0);
        if ($storage.length)   totalPrice += parseFloat($storage.data("price") || 0);

        // Show summary
        $("#summary-condition").text($condition.length ? $condition.val() : "-");
        $("#summary-carrier").text($carrier.length ? $carrier.val() : "-");
        $("#summary-storage").text($storage.length ? $storage.val() : "-");
        $("#summary-accessories").text(accessories.length ? accessories.join(", ") : "None");
        $("#summary-price").text(totalPrice.toFixed(2));

        // Hide form, show result
        $("#single-page-step-form").hide();
        $("#result-box").show();
    });

    // Show first step initially
    showStep(currentStep);

});
