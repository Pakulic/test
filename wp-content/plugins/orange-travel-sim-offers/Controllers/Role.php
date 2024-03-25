<?php


namespace Controllers;


class Role
{
    private array   $capabilities;
    public function __construct()
    {
        $this->capabilities = array(
            'edit_esim_purchase_offer',
            'read_esim_purchase_offer',
            'delete_esim_purchase_offer',
            'edit_esim_purchase_offers',
            'edit_others_esim_purchase_offers',
            'read_others_esim_purchase_offers',
            'publish_esim_purchase_offers',
            'publish_pages_esim_purchase_offers',
            'read_private_esim_purchase_offers',
            'create_private_esim_purchase_offers',
            'edit_published_esim_purchase_offers',
            'delete_esim_purchase_offers',
            'delete_published_esim_purchase_offers',
            'delete_others_esim_purchase_offers',
            'edit_private_esim_purchase_offers',
            'delete_private_esim_purchase_offers',

            'edit_esim_recharge_offer',
            'read_esim_recharge_offer',
            'delete_esim_recharge_offer',
            'edit_esim_recharge_offers',
            'edit_others_esim_recharge_offers',
            'read_others_esim_recharge_offers',
            'publish_esim_recharge_offers',
            'publish_pages_esim_recharge_offers',
            'read_private_esim_recharge_offers',
            'create_private_esim_recharge_offers',
            'delete_esim_recharge_offers',
            'edit_published_esim_recharge_offers',
            'delete_published_esim_recharge_offers',
            'delete_others_esim_recharge_offers',
            'edit_private_esim_recharge_offers',
            'delete_private_esim_recharge_offers',

        );
    }
    public function plugin_sim_add_capabilities($roleType)
    {
        $role = get_role($roleType);
        if ($role) {
            foreach ($this->capabilities as $capabilitie) {
                $role->add_cap($capabilitie);
            }
        }
    }
    public function plugin_sim_remove_capabilities($roleType)
    {
        $role = get_role($roleType);
        if ($role) {
            foreach ($this->capabilities as $capabilitie) {
                $role->remove_cap($capabilitie);
            }
        }
    }

    function plugin_sim_update_users_capabilities()
    {
        $allRoles = wp_roles()->roles;
        $rolesList = array_keys($allRoles);

        $userRightsObject = get_field_object('field_65ba467cdfd1d', 'option');
        $userRights = get_field('field_65ba467cdfd1d', 'option');
        $userRightsDiff = array_diff(array_values($userRightsObject['choices']), $userRights);

        /* attribution des droits aux roles définis dans la page  */
        if (count($userRights) > 0) {
            foreach ($userRights as $userRight) {
                $this->plugin_sim_add_capabilities($userRight);
            }
        }
        /* attribution des droits aux role non définis dans la page  */
        if (count($userRightsDiff) > 0) {
            foreach ($userRightsDiff as $userRightDiff) {
                $this->plugin_sim_remove_capabilities($userRightDiff);
            }
        }
        /* attribution des droits au role admin */
        if (get_role('administrator')) {
            $this->plugin_sim_add_capabilities('administrator');
        }

        return  $rolesList;
    }

    function plugin_sim_update_role_choices($rolesList)
    {
        if (get_field('update_role_lists', 'option') === true) {
            $userRightsObject = get_field_object('field_65ba467cdfd1d', 'option');
            if ($userRightsObject) {
                $userRightsObjectDiff = array_diff($rolesList, $userRightsObject['choices']);
                if (count($userRightsObjectDiff) > 0) :
                    $rolesListField = array_splice($rolesList, 1);
                    $userRightsObject['choices'] = $rolesListField;
                    acf_update_field($userRightsObject);
                endif;
            }
        }
    }
}
