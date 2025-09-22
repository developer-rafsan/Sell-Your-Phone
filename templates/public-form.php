<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $product;
$product_id = $product->get_id();

// Custom meta fetch
$types = ['storage','carrier','condition'];
$custom_data = [];
foreach ( $types as $type ) {
    $meta_key = "_pixelcode_{$type}_rows";
    $rows = get_post_meta( $product_id, $meta_key, true ); 
    if ( !empty($rows) && is_array($rows) ) {
        $custom_data[$type] = $rows;
    } else {
        $custom_data[$type] = [];
    }
}
?>

<form id="single-page-step-form" method="post" action="" enctype="multipart/form-data">
    <input type="hidden" id="product_id" value="<?php echo esc_attr($product_id); ?>">

    <!-- Step 1: Condition -->
    <div class="step active-step">
        <h1>How’s your phone feeling today?</h1>
        <div class="line"></div>
        <?php
        if ( !empty($custom_data['condition']) ) {
            foreach ( $custom_data['condition'] as $index => $row ) {
                $id    = esc_attr($row['condition']);  
                $title = esc_html($row['title']);    
                $price = esc_html($row['price']);   
                $desc  = esc_html($row['description']); 

                echo "<div class='condition-option'>
                        <input type='radio' id='condition_{$id}' data-price='{$price}' name='phone_condition' value='{$title}' " . checked($index, 0, false) . ">
                        <label for='condition_{$id}'>
                            <h6>{$title}</h6>
                            <p>{$desc}</p>
                        </label>
                    </div>";
            }
        }
        ?>
        <div class="step-nav">
            <button type="button" class="step-btn next">Next Step</button>
        </div>
    </div>

    <!-- Step 2: Carrier -->
    <div class="step">
        <h1>Please select the phone's carrier?</h1>
        <div class="line"></div>
        <?php
        if ( !empty($custom_data['carrier']) ) {
            foreach ( $custom_data['carrier'] as $index => $row ) {
                $id    = esc_attr($row['carrier']);  
                $title = esc_html($row['title']);    
                $price = esc_html($row['price']); 

                echo "<div class='carrier-option'>
                        <input type='radio' id='carrier_{$id}' data-price='{$price}' name='phone_carrier' value='{$title}' " . checked($index, 0, false) . ">
                        <label for='carrier_{$id}'>
                            <h6>{$title}</h6>
                        </label>
                    </div>";
            }
        }
        ?>
        <div class="step-nav">
            <button type="button" class="step-btn prev">Previous</button>
            <button type="button" class="step-btn next">Next</button>
        </div>
    </div>

    <!-- Step 3: Storage -->
    <div class="step">
        <h1>What is the phone's storage capacity?</h1>
        <div class="line"></div>
        <?php
        if ( !empty($custom_data['storage']) ) {
            foreach ( $custom_data['storage'] as $index => $row ) {
                $id    = esc_attr($row['storage']);  
                $title = esc_html($row['title']);    
                $price = esc_html($row['price']); 

                echo "<div class='storage-option'>
                        <input type='radio' id='storage_{$id}' data-price='{$price}' name='phone_storage' value='{$title}' " . checked($index, 0, false) . ">
                        <label for='storage_{$id}'>
                            <h6>{$title}</h6>
                        </label>
                    </div>";
            }
        }
        ?>
        <div class="step-nav">
            <button type="button" class="step-btn prev">Previous</button>
            <button type="button" class="step-btn next">Next</button>
        </div>
    </div>

    <!-- Step 4: Accessories -->
    <div class="step">
        <h1>What accessories will be included?</h1>
        <div class="line"></div>
        <div class='included-option'>
            <input type="checkbox" id="acc-charger" data-price="5" name="accessories[]" value="Charger">
            <label for="acc-charger"><h6>Charger</h6></label>
        </div>

        <div class='included-option'>
            <input type="checkbox" id="acc-earphones" data-price="7" name="accessories[]" value="Earphones">
            <label for="acc-earphones"><h6>Earphones</h6></label>
        </div>

        <div class='included-option'>
            <input type="checkbox" id="acc-original-box" data-price="10" name="accessories[]" value="Original Box">
            <label for="acc-original-box"><h6>Original Box</h6></label>
        </div>

        <div class="step-nav">
            <button type="button" class="step-btn prev">Previous</button>
            <button type="button" class="step-btn next">Next</button>
        </div>
    </div>

    <!-- Step 5: Upload Images -->
    <div class="step">
        <h1>Upload Images of your phone</h1>
        <div class="line"></div>
        <p class="help">You can upload multiple images (jpg, png, gif).</p>

        <div class="upload-images-wrapper">
            <label class="upload-label" for="phone_images">
                <span>Click or Drag & Drop to upload images</span>
                <input type="file" id="phone_images" name="phone_images[]" accept="image/*" multiple>
            </label>
            <div class="uploaded-preview" style="margin-top:10px;"></div>
        </div>

        <div class="step-nav">
            <button type="button" class="step-btn prev">Previous</button>
            <button type="submit" class="step-btn submit">Submit</button>
        </div>
    </div>
</form>

<!-- Result -->
<div id="result-box" style="display:none;">
    <div class="result-content">
        <h2>Your device is valued at</h2>
        <div class="line"></div>
        <h3>Total Price: $<span id="summary-price"></span></h3>
        <div class="button-group">
            <button class="back">Back</button>
            <button class="add-to-box">Add to Box</button>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
    let currentStep = 0;
    const steps = $(".step");

    function showStep(index){
        steps.removeClass("active-step");
        steps.eq(index).addClass("active-step");
    }

    $(".next").click(function(){ if(currentStep < steps.length-1){ currentStep++; showStep(currentStep); }});
    $(".prev").click(function(){ if(currentStep > 0){ currentStep--; showStep(currentStep); }});

    // Image preview
    $("#phone_images").on("change", function(){
        const previewContainer = $(".uploaded-preview");
        previewContainer.empty();
        const files = this.files;

        if(files.length > 0){
            for(let i = 0; i < files.length; i++){
                const reader = new FileReader();
                reader.onload = function(e){
                    const img = $("<img>").attr("src", e.target.result).css({
                        width: "100px",
                        height: "100px",
                        margin: "5px",
                        objectFit: "cover",
                        border: "1px solid #ddd",
                        padding: "2px"
                    });
                    previewContainer.append(img);
                }
                reader.readAsDataURL(files[i]);
            }
        }
    });

    // Submit -> calculate total
    $(".submit").click(function(e){
        e.preventDefault();

        let total = 0;
        total += parseFloat($("input[name='phone_condition']:checked").data("price") || 0);
        total += parseFloat($("input[name='phone_carrier']:checked").data("price") || 0);
        total += parseFloat($("input[name='phone_storage']:checked").data("price") || 0);
        $("input[name='accessories[]']:checked").each(function(){
            total += parseFloat($(this).data("price") || 0);
        });

        $("#summary-price").text(total);
        $("#single-page-step-form").hide();
        $("#result-box").show();
    });

    // Back
    $("#result-box .back").click(function(){
        $("#result-box").hide();
        $("#single-page-step-form").show();
    });

    // Add to Box (AJAX add to cart with images)
    $("#result-box .add-to-box").click(function(e){
        e.preventDefault();

        let product_id   = $("#product_id").val();
        let condition    = $("input[name='phone_condition']:checked").val();
        let carrier      = $("input[name='phone_carrier']:checked").val();
        let storage      = $("input[name='phone_storage']:checked").val();
        let accessories  = [];
        $("input[name='accessories[]']:checked").each(function(){ accessories.push($(this).val()); });
        let price        = $("#summary-price").text();

        let formData = new FormData();
        formData.append("action", "custom_add_to_cart");
        formData.append("product_id", product_id);
        formData.append("condition", condition);
        formData.append("carrier", carrier);
        formData.append("storage", storage);
        formData.append("price", price);

        accessories.forEach(acc => formData.append("accessories[]", acc));

        // ✅ Correct image input
        const files = $("#phone_images")[0].files;
        for(let i = 0; i < files.length; i++){
            formData.append("phone_images[]", files[i]);
        }

        // Debug formData
        for (let pair of formData.entries()) {
            console.log(pair[0] + ": ", pair[1]);
        }

        $.ajax({
            type: "POST",
            url: wc_add_to_cart_params.ajax_url,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){                
                if(response.success){
                    console.log(response);
                    window.location.href = "<?php echo wc_get_cart_url(); ?>";
                } else {
                    alert("❌ " + response.data.message);
                }
            }
        });
    });
});
</script>