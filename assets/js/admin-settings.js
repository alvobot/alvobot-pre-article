jQuery(document).ready(function($) {
    // Function to generate random hex color
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Function to create new CTA box
    function createCTABox(index, translations) {
        const randomColor = getRandomColor();
        return `
            <div class="cta-box">
                <h3>${translations.cta} ${index}</h3>
                <div class="cta-field">
                    <label>${translations.buttonText}</label>
                    <input 
                        type="text" 
                        name="alvobot_pre_artigo_options[button_text_${index}]" 
                        value="" 
                        class="regular-text"
                    />
                </div>
                <div class="cta-field">
                    <label>${translations.buttonColor}</label>
                    <input 
                        type="text" 
                        name="alvobot_pre_artigo_options[button_color_${index}]" 
                        value="${randomColor}" 
                        class="wp-color-picker-field" 
                        data-default-color="${randomColor}" 
                    />
                </div>
            </div>
        `;
    }

    // Handle CTA quantity changes
    $('#num_ctas').on('input', function() {
        const numCTAs = parseInt($(this).val()) || 0;
        const container = $('#ctas_container');
        const currentCTAs = container.children('.cta-box').length;

        if (numCTAs > currentCTAs) {
            // Add new CTA boxes
            for (let i = currentCTAs + 1; i <= numCTAs; i++) {
                const newCTA = $(createCTABox(i, alvobotTranslations));
                container.append(newCTA);
                // Initialize color picker for new elements
                newCTA.find('.wp-color-picker-field').wpColorPicker();
            }
        } else if (numCTAs < currentCTAs) {
            // Remove excess CTA boxes
            container.children('.cta-box').slice(numCTAs).remove();
        }
    });

    // Initialize existing color pickers
    $('.wp-color-picker-field').wpColorPicker();
});
