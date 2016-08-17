<?php
/**
 * Ninja Forms - X2CRM
 *
 * X2CRM API Action Class
 *
 * @package     Ninja Forms - X2CRM
 * @author      Raymond Colebaugh <raymond@x2engine.com>
 * @copyright   2016 X2Engine, Inc.
 * @license     GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class NF_Action_X2CRMAction extends NF_Notification_Base_Type {
    static $defaultFields = array(
        'address',
        'address2',
        'assignedTo',
        'backgroundInfo',
        'city',
        'closedate',
        'company',
        'contactId',
        'country',
        'createDate',
        'dealstatus',
        'dealvalue',
        'description',
        'doNotCall',
        'doNotEmail',
        'dupeCheck',
        'email',
        'escalatedTo',
        'expectedCloseDate',
        'facebook',
        'fingerprintId',
        'firstName',
        'googleplus',
        'impact',
        'interest',
        'lastName',
        'leadDate',
        'leadscore',
        'leadSource',
        'leadstatus',
        'leadtype',
        'linkedin',
        'mainIssue',
        'name',
        'nextAction',
        'origin',
        'otherUrl',
        'parentCase',
        'phone',
        'phone2',
        'priority',
        'rating',
        'resolution',
        'skype',
        'state',
        'status',
        'subIssue',
        'subject',
        'timezone',
        'title',
        'trackingKey',
        'twitter',
        'visibility',
        'website',
        'zipcode',
    );

    /**
     * Define custom action name
     */
    function __construct() {
        $this->name = __( 'X2CRM API Action', 'ninja-forms' );
    }

    /**
     * Output our edit screen
     *
     * @access public
     * @since 2.8
     * @return void
     */
    public function edit_screen($id = '') {
        $endpoint = Ninja_Forms()->notification( $id )->get_setting( 'endpoint' );
        $apiUser = Ninja_Forms()->notification( $id )->get_setting( 'apiUser' );
        $apiPassword = Ninja_Forms()->notification( $id )->get_setting( 'apiPassword' );
        $model = Ninja_Forms()->notification( $id )->get_setting( 'model' );
        $form_id = $_GET['form_id'];
        $fields = array_merge(
            array(array('id' => null, 'data' => array('label' => ''))),
            ninja_forms_get_fields_by_form_id($form_id)
        );
        $availableModels = array(
            'Contacts',
            'Services',
            'weblead',
        );
        ?>

        <tr>
            <th scope="row"><label for="settings-endpoint"><?php _e( 'API Endpoint' ); ?></label></th>
            <td><input type="text" name="settings[endpoint]" id="settings-endpoint" value="<?php echo esc_attr( $endpoint ); ?>" class="regular-text"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="settings-apiUser"><?php _e( 'API User' ); ?></label></th>
            <td><input type="text" name="settings[apiUser]" id="settings-apiUser" value="<?php echo esc_attr( $apiUser ); ?>" class="regular-text"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="settings-apiPassword"><?php _e( 'API Password' ); ?></label></th>
            <td><input type="text" name="settings[apiPassword]" id="settings-apiPassword" value="<?php echo esc_attr( $apiPassword ); ?>" class="regular-text"/></td>
        </tr>
        <tr>
            <th scope="row"><label for="settings-model"><?php _e( 'Model' ); ?></label></th>
            <td>
                <select name="settings[model]" id="settings-model" class="regular-text">
                <?php
                    foreach ($availableModels as $m) {
                        $selected = ($m === $model) ? ' selected="selected"' : '';
                        ?><option<?php echo $selected; ?>><?php echo $m; ?></option><?php
                    }
                ?>
                </select>
            </td>
        </tr>

        <tr><th scope="row"><?php _e( 'X2CRM to Ninja Forms Field Mapping' ); ?></th></tr>
        <?php foreach (self::$defaultFields as $x2field) { ?>
            <tr>
                <th scope="row"><label for="settings-x2<?php echo $x2field ?>"><?php _e( $x2field ); ?></label></th>
                <td>
                    <select name="settings[x2<?php echo $x2field ?>]" id="settings-x2<?php echo $x2field ?>" class="regular-text">
                    <?php
                        foreach ($fields as $field) {
                            if (!(is_array($field) && array_key_exists('id', $field) && array_key_exists('data', $field)))
                                continue;
                            $setting = Ninja_Forms()->notification( $id )->get_setting('x2'.$x2field);
                            $selected = ($field['id'] === $setting) ? ' selected="selected"' : '';
                            ?><option<?php echo $selected; ?> value="<?php echo $field['id'] ?>"><?php echo $field['data']['label']; ?></option><?php
                        }
                    ?>
                    </select>
                </td>
            </tr><?php 
        }
        ?></th><?php
    }

    /**
     * Process our Redirect notification
     *
     * @access public
     * @since 2.8
     * @return void
     */
    public function process($id) {
        global $ninja_forms_processing;
        $endpoint = Ninja_Forms()->notification( $id )->get_setting( 'endpoint' );
        $apiUser = Ninja_Forms()->notification( $id )->get_setting( 'apiUser' );
        $apiPassword = Ninja_Forms()->notification( $id )->get_setting( 'apiPassword' );
        $model = Ninja_Forms()->notification( $id )->get_setting( 'model' );
        $auth = $apiUser.":".$apiPassword;
        $map = $this->getFieldMap($id);

        // Process Ninja Forms submission data
        $postData = array();
        $values = $ninja_forms_processing->get_all_fields();
        foreach ($values as $i => $value) {
            if (array_key_exists($i, $map))
                $postData[$map[$i]] = $value;
        }
        if (array_key_exists('x2_key', $_POST) && !empty($_POST['x2_key']))
            $postData['trackingKey'] = $_POST['x2_key'];

        $this->postApi($auth, $endpoint, $model, $postData);
    }

    /**
     * Retrieve a mapping of Ninja Forms field ids to X2CRM field names
     */
    protected function getFieldMap($id) {
        $x2FieldMap = array();
        foreach (self::$defaultFields as $field) {
            $setting = Ninja_Forms()->notification( $id )->get_setting( 'x2'.$field );
            if (!empty($setting))
                $x2FieldMap[$field] = $setting;
        }
        $fieldMap = array_flip($x2FieldMap);
        return $fieldMap;
    }

    /**
     * POST model data to X2CRM
     */
    protected function postApi($auth, $endpoint, $model, $data) {
        // Set visibility if unset
        if (!array_key_exists ('visibility', $data))
            $data['visibility'] = 1;

        return wp_safe_remote_post("$endpoint/$model", array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($auth),
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
        ));
    }
}

return new NF_Action_X2CRMAction();
?>
