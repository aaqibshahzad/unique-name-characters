jQuery(document).ready(function() {
    
    jQuery('#abnUserName').on('input', function() {
        var inputVal = jQuery(this).val();
        var letterCounts = {};
        var errorMessage = "You cannot enter the same letter more than 3 times.";
    
        for (var i = 0; i < inputVal.length; i++) {
            var letter = inputVal[i];
            if (!letterCounts[letter]) {
                letterCounts[letter] = 1;
            } else {
                letterCounts[letter]++;
            }
            
            if (letterCounts[letter] > 3) {
                alert(errorMessage);
                jQuery(this).val(inputVal.substring(0, i) + inputVal.substring(i + 1));
                break;
            }
        }
    });    

    if (jQuery('#ModalPersonnalisation').length) {
        jQuery('.single_add_to_cart_button').prop('disabled', true)
    }

    jQuery('#personalizeCheckbox').on('click', function() {
        var personalizeCheckbox = jQuery('#personalizeCheckbox').val()
        if (personalizeCheckbox == 'on') {
            jQuery('#ModalPersonnalisation').show()
            jQuery('#personalizeCloseCheckbox').prop('checked', true);
        }
    })

    jQuery('#personalizeCloseCheckbox').on('click', function() {
        var personalizeCloseCheckbox = jQuery('#personalizeCloseCheckbox').val()
        if (personalizeCloseCheckbox == 'on') {
            jQuery('#ModalPersonnalisation').hide()
            jQuery('#personalizeCheckbox').removeAttr('checked')
        }
    })

    jQuery('#abnUserName').on('keyup', function() {
        jQuery('#maxLengthCount').html('')
        var totalLength = 15
        var numberOfLetters = jQuery('#abnUserName').val().length
        var remainingNumber = totalLength - numberOfLetters
        jQuery('#maxLengthCount').html(remainingNumber + '/15')
        jQuery('#maxLengthCount').show()
        jQuery('#abnConfirmNameBtn').removeAttr('disabled')
    })

    jQuery('#abnConfirmNameBtn').on('click', function(e) {
        e.preventDefault();
        var abnUserName = jQuery('#abnUserName').val();
        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_animals_name_by_user_name',
                abnUserName: abnUserName
            },
            success: function(response) {
                if (response.success == true) {
                    if (response.data !== null) {
                        let allUserItems = '';
                        let letterContainer = `<div class="abn-radio-buttons">`;
    
                        Object.keys(response.data).forEach((key) => {
                            const regex = /^\d+_/;
                            let latter = key.replace(regex, '');
                            console.log(latter)
                            let images = response.data[key];
                            let hasMultipleImages = images.length > 1; 
                            
                            letterContainer += `
                                <div class="abn-letter-group">
                                    <span class="abn-letter-label">${latter.toUpperCase()}</span>`;
                            
                            if (hasMultipleImages) {
                                letterContainer += `
                                    <div class="swiper abn-swiper-${latter}">
                                        <div class="swiper-wrapper">`;
                            } else {
                                letterContainer += `<div class="abn-single-item">`;
                            }
    
                            images.forEach((image) => {
                                letterContainer += `
                                    <div class="${hasMultipleImages ? 'swiper-slide' : 'abn-slide-single'}">
                                        <label class="abn-custom-radio">
                                            <input type="radio" name="animal_for_${key}" value="${image.animal_name}">
                                            <span class="abn-radio-btn">
                                                <i class="abn-las la-check"></i>
                                                <div class="abn-hobbies-icon">
                                                    <img src="${image.animal_image}" width="50" height="50">
                                                    <p>${toCapitalized(image.animal_name)}</p>
                                                </div>
                                            </span>
                                        </label>
                                    </div>`;
                            });
    
                            letterContainer += `</div>`;
                            if (hasMultipleImages) {
                                letterContainer += `</div>`;
                            }
                            letterContainer += `</div>`;
                        });
    
                        letterContainer += `</div>`;
                        allUserItems += letterContainer;
                        document.getElementById('animal-by-name-list').innerHTML = allUserItems;
                        
                        initializeSwipers();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', status, error);
            }
        });
    });
    

    jQuery('#abnValidateNameBtn').on('click', function(e) {
        jQuery('.single_add_to_cart_button').removeAttr('disabled')
        jQuery('#ModalPersonnalisation').hide()
    })

    function toCapitalized(str) {
        return str
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')
    }
})

function initializeSwipers() {
  for (let i = 0; i < 26; i++) {
    let letter = String.fromCharCode(97 + i);
    let swiperClass = `.abn-swiper-${letter}`;

    if (document.querySelector(swiperClass)) {
      new Swiper(swiperClass, {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: false,
        breakpoints: {
           320: {
                slidesPerView: 1.3,
                spaceBetween: 10,
            },
            480: {
                slidesPerView: 1.5,
                spaceBetween: 15,
            },
            640: {
                slidesPerView: 1.5,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2.3,
                spaceBetween: 10,
            },
            1024: {
                slidesPerView: 2.6,
                spaceBetween: 10,
            },
        },
      });
    }
  }
}

function reinitializeSwipers() {
  Swiper.instances.forEach(instance => instance.destroy(true, true));
  initializeSwipers();
}
