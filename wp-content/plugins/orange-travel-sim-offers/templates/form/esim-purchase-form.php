<?php
if ($query->have_posts()) :
?>
    <div id="sim-purchase-form" class="widget-sim-form">
        <label class="gb-headline gb-headline-text" for="sim-purchase-input"><?php the_field($title, 'option'); ?></label>
        <div class="select">
            <div class="selectHeader">
                <button type="button" id="selectBtn"
                        data-nonce="<?php echo wp_create_nonce('plugin_sim_countries_list'); ?>"
                        data-action="plugin_sim_countries_list"
                        data-ajaxurl="<?php echo admin_url( 'admin-ajax.php' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="0.9em" height="0.9em" fill="#000000" class="solaris-icon si-international" viewBox="0 0 1000 1000">
                        <path d="M502.343 75.009C501.4 75 499.447 75 499.447 75c-233.368.017-423.335 188.418-424.87 422.165C73 731.884 261.979 923.422 496.683 924.992c.963.008 1.909 0 2.871 0 233.367 0 423.326-188.427 424.9-422.166C926 268.1 737.021 76.569 502.343 75.009M456 127.444v23.538a24.27 24.27 0 0 1-6.169 14.9l-40.008 40.012a24.28 24.28 0 0 1-14.892 6.169h-78.516a8.753 8.753 0 0 0-8.725 8.725v34.9a8.75 8.75 0 0 0 8.725 8.725h78.518a8.753 8.753 0 0 1 8.724 8.725v34.909a8.75 8.75 0 0 1-8.724 8.725h-34.9a24.3 24.3 0 0 0-14.893 6.169l-31.284 31.288a24.3 24.3 0 0 0-6.169 14.894v34.9a24.27 24.27 0 0 1-6.168 14.894L270.237 450.2a24.3 24.3 0 0 1-14.892 6.169h-34.9a24.3 24.3 0 0 1-14.893-6.169l-31.284-31.288a24.3 24.3 0 0 0-14.893-6.169h-24.6C171.05 261.716 299.111 145.742 456 127.444M203 729.405v-20a24.3 24.3 0 0 0-6.168-14.894l-31.284-31.288a24.28 24.28 0 0 1-6.169-14.894V569.8a24.3 24.3 0 0 1 6.169-14.894l40.008-40.013a24.3 24.3 0 0 1 14.893-6.169h26.173a24.3 24.3 0 0 1 14.892 6.169l83.63 83.638a24.3 24.3 0 0 0 14.893 6.169h34.9a8.753 8.753 0 0 1 8.724 8.725v82.011a24.3 24.3 0 0 1-6.169 14.894l-36.524 36.52a24.24 24.24 0 0 0-6.168 14.894v31.415a24.3 24.3 0 0 1-6.168 14.894l-23.8 23.807A377.5 377.5 0 0 1 203 729.405m471.253 102.281L654.1 811.544a24.28 24.28 0 0 1-6.168-14.894V665.764a24.28 24.28 0 0 0-6.168-14.893l-40.009-40.013a24.3 24.3 0 0 0-14.892-6.17h-87.239a8.75 8.75 0 0 1-8.725-8.725v-78.517a24.3 24.3 0 0 1 6.168-14.894l40.009-40.013a24.3 24.3 0 0 1 14.893-6.169h87.242a24.28 24.28 0 0 0 14.889-6.17l40.009-40.013a24.3 24.3 0 0 1 14.893-6.169h26.173a8.75 8.75 0 0 0 8.724-8.725v-26.174a8.753 8.753 0 0 0-8.724-8.725h-26.17a24.3 24.3 0 0 0-14.893 6.168L654.1 406.576a24.3 24.3 0 0 1-14.892 6.168l-86.765-.008a8.744 8.744 0 0 1-8.724-8.725v-34.9a24.4 24.4 0 0 1 6.143-14.919l39.582-39.962a24.13 24.13 0 0 1 14.867-6.195h78.518a8.75 8.75 0 0 0 8.725-8.725v-26.176a8.753 8.753 0 0 0-8.725-8.725h-34.9a8.75 8.75 0 0 1-8.724-8.725v-34.9a8.753 8.753 0 0 1 8.724-8.725l91.818.009c80.273 67.108 132 167.089 134.671 278.862l-28.387-28.39a24.3 24.3 0 0 0-14.893-6.169h-26.166a8.753 8.753 0 0 0-8.725 8.725V500a24.28 24.28 0 0 0 6.168 14.894l31.285 31.275a24.3 24.3 0 0 1 6.168 14.894v34.9a24.28 24.28 0 0 1-6.168 14.895l-31.285 31.287a24.3 24.3 0 0 0-6.168 14.894v71.8a378.75 378.75 0 0 1-121.994 102.847" style="fill-rule:evenodd" fill="#000000"></path>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="0.9em" height="0.9em" fill="#000000" class="solaris-icon si-form-chevron-down" viewBox="0 0 1000 1000">
                        <path d="M208 396.286 416.214 604.5l83.286 83.286 83.286-83.286L791 396.286 707.714 313 499.5 521.214 291.286 313z" fill="#000000"></path>
                    </svg>
                </button>
                <form action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST" id="searchform" class="searchform relative" name="ajaxForm">
                    <input type="search" name="s" id="countrieslist" placeholder="<?php the_field($placeholder, 'option'); ?>" />
                    <input
                            type="hidden"
                            name="nonce"
                            value="<?= wp_create_nonce( 'plugin_sim_instant_search' ); ?>"
                    >
                    <input
                            type="hidden"
                            name="action"
                            value="plugin_sim_instant_search"
                    >
                </form>
            </div>
            <div id="selectChild" class="none">
                <div id="results"  class="select-child">
                </div>
            </div>
        </div>
        <div>
            <input type="hidden" class="domain" name="domain" value="<?= WIDGET_SIM_DOMAIN ?>" />
            <button id="countryLinkBtn" type="button" class="widget-sim-btn"><?php the_field($buttonName, 'option'); ?></button>
        </div>
    </div>


<?php
endif;


