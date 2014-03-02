<?php
/** ContactUs class
* This is what is executed when a user accesses Special:ContactUs
*/
if (!defined('MEDIAWIKI'))
    die("Not a valid entry point.");

class SpecialContactUs extends FormSpecialPage {
    /**
     * @var \User A user object to work with.
     */
    protected $user;
    /**
     * @var array People who will receive emails
     */
    private $recipients;
    /**
     * @var array Recipient groups
     */
    private $groups;
    /**
     * @var string permission string
     */
    private $perm;
    /**
     * Constructor. Sets up the User object, then
     * calls the parent's constructor.
      */

    function __construct(){
        global $wgGroupPermissions;
        $wgGroupPermissions['sysop']['contactus-admin'] = true;
        // Make $this->user into a user object
        $this->user = $this->getUser();
        // Set permission for administration
        $this->perm = 'contactus-admin';
        // And get the result of the parent constructor.
		parent::__construct( 'ContactUs' );
    }

    /**
     * Gathers settings from MediaWiki pages
     * @param string $setting
     * @return Content|null|string
     */
    public function load_user_settings($setting){
        // We can't do anything if that parameter is null.
        if ($setting == '')
            return '';
        $page = Title::newFromText($setting, NS_MEDIAWIKI);
        if (!$page->exists())
            return '';
        else {
            $page = wikiPage::factory($page);
            $cont = $page->getContent();
            $cont = $cont->mText;
        }
        return $cont;
    }

    /**
     * Gathers all settings information from the mediawiki pages
     * @return array $settings
     *
     */
    public function load_all_settings(){
        $return = array();
        $users = $this->load_user_settings('Contactus_users');

        $separate = explode('<br/>', $users);
        $x = 0;
        foreach ($separate as $people){
            $name = explode('=', $people);
            $return['user'][$x]['name'] = $name[0];
            $groups = explode('|', $name[1]);
            $return['user'][$x]['groups'] = $groups;
            $x++;
        }

        $groups = $this->load_user_settings('Contactus_groups');
        $group = '';
        if ($groups == '')
            $group = false;
        if ($group != false && $group != ''){
            $group = explode('<br/>', $groups);
        }
        // If any of these are true, groups aren't set, so we'll throw an error.
        if (($group == false || empty($group))){

        }

        $custom = $this->load_user_settings('Contactus-custom-message');
        if ($custom != '')
            $return['custom_message'] = $custom;

        $reasons = $this->load_user_settings('Contactus-contact-reasons');
        if ($reasons != ''){
            $return['reasons'] = $reasons;
        }
        return $return;

    }
    /**
     * This checks permissions and throws a mwException if they are insufficient
     */
    public function checkPermissions($perm){
        if (!$this->user->isAllowed([$perm]))
            $this->displayRestrictionError();
    }
    /**
     * This function handles the extension's output
     * @param string $type Type of form to build
     */
    protected function build_form($type){
        global $wgScriptPath;
        $output = $this->getOutput();
        if ($type == 'email'){
            $settings = $this->load_all_settings();


             Xml::openElement('p', array('id' => 'contactus-msg'));
             ($settings['custom_message'] && $settings['custom_message'] != '')?
                $output->addWikiText($settings['custom_message']):
                $output->addWikiMsg('contactus-page-desc');
            Xml::closeElement("p");
            Xml::openElement("div", array('id' => 'contactus_form_wrapper', 'style' => 'margin:0 auto'));
            $stuff = $this->getFormFields();
            $this->getForm($stuff)->displayForm($sturrrrrrr = null);


            /*
            Html::openElement('form', array('name' => 'contactus_form', 'method' => 'post', 'id' => 'contactus_form', 'action' => 'index.php'));
            Xml::openElement('label', array('for' => 'user-email'));
            $output->addWikiMsg('contactus-your-email');
            Xml::closeElement('label');
            $output->addElement("input", array("type" => 'text', "size" => 60, 'name' => 'user-email', 'id' => 'contactus_email_input'));
            Xml::openElement('label', array('for' => 'user-alias'));
            $output->addWikiMsg('contactus-your-username');
            Xml::closeElement('label');
            $output->addElement("input", array("type" => 'text', "size" => 60, 'name' => 'user-alias', 'id' => 'contactus_alias_input'));
            $output->addWikiMsg('contactus-problem-question');
            $output->addHtml("<select name=\"contact_reason\" id=\"contactus-contact-reason\">");
            if ($settings['custom_reasons']=='')
                $settings['custom_reasons'] = array('tech' => 'Report a bug or issue', 'affiliate' => 'Request affiliation', 'administration' => 'Contact an admin about site affairs', 'other' => 'Other');
            foreach ($settings['custom_reasons'] as $key => $val){
                $output->addElement('option', array("value" => $key),$val);
            }
            $output->addHTML('</select><br/>');
            $output->addWikiMsg('contactus-subject');
            $output->addElement('input', array('name' => 'message-subject', 'id' => 'contactus-subject-box', 'style' => 'width:500px'));
            $output->addWikiMsg('contactus-message');
            $output->addElement('input', array('name' => 'message-body', 'id' => 'contactus-message-box', 'style' => 'text-align:left;height:200px;width:500px'));
            $output->addHTML('<br/>');
            $output->addHtml(Xml::submitButton('Send Email'));
            Xml::closeElement('form');
            */
            Xml::closeElement('div');
        }
        elseif ($type == 'settings'){
            $text = '{|class="wikitable" id="contactus-settings-table"| '.wfMessage('contactus-table-settings')->text() . '
                 | '.wfMessage('contactus-table-variable')->text() . '
                 | '.wfMessage('contactus-table-value')->text() . '
                 | '.wfMessage('contactus-table-page')->text() . '
                 |-
                 | '.wfMessage('contactus-table-users')->text() . '
                 | '.$user.'
                 | [[MediaWiki:Contactus_users]]
                 |-
                 | '.wfMessage('contactus-table-groups')->text() . '
                 | '.$group.'
                 | [[MediaWiki:Contactus_groups]]
                 |-
                 |style="colspan:4;" | Other
                 | '.wfMessage('contactus-table-custom')->text() . '
                 |
                 |}';
            Xml::openElement('p', array('id' => 'contactus-settings-msg'));
            $output->addWikiMsg('contactus-settings-msg');
            Xml::closeElement('p');
            $output->addWikiText($text);
        }
        elseif ($type == 'success'){
            $output->addWikiMsg('contactus-email-sent');
        }
    }

    /**
     * Resolve what the user is trying to do
     * @param null|string $par (intended to be called via $this->execute, with $par from that function as input)
     * @return string telling us what the user is trying to do
     */
    protected function resolve_request($par){
        $request = $this->getRequest();
        $req = array('actions' => '');
        $par == '' ? $req['type'] = 'email' : null ;
        ($request->getText('action') == 'submit' && strtolower($par) == 'settings') ? array_merge($req['actions'], array('submit')) : null;
        ($request->getText('status') == 'success' && strtolower($par) == '') ? array_merge($req['actions'], array('success')) : null;
        strtolower($par) == 'settings' ? $req['type'] = 'settings' : null;
        return $req;
    }
    /**
     * This function actually sends the email.
     */
    protected function send_mail($recipients, $sender, $subject, $body){
        if (empty($recipients))
            throw new mwException(wfMessage('contactus-no-recipients'));
        preg_match('/^.*\@.*\..*$/', $sender, $matches);
        if ($sender == '')
            throw new mwException(wfMessage('contactus-no-email'));
        if (empty($matches))
            throw new mwException(wfMessage('contactus-bad-email'));
        // Truly and finally send the emails
        $status = userMailer::send($recipients, $sender, $subject, $body);
    }

    /**
     * Create the values to pass to $this->getForm()
     * for the <select> tag
     * @return Array
     */
    protected function getFormFields(){
        $reasons = $this->load_user_settings('contact_reasons');
        $reasons = explode("\n", $reasons);
        $x = 0;
        $master = '';
        foreach ($reasons as $values){
            $boom = explode('|', $values);
            $master[$x][$boom[0]] = $boom[1];
        }
        $formDescriptor = array(
            'user-email' => array(
                'label-message' => 'contactus-your-email',
                'type' => 'text'
            ),
            'username' => array(
                'label-message' => 'contactus-your-username',
                'class' => 'HTMLTextField', // same as type 'text'
            ),
/*            'problem-or-question' => array(
                'type' => 'select',
                'label-message' => 'contactus-problem-question',

            ),
*/            'subject' => array(
                'class' => 'HTMLTextField',
                'label-message' => 'contactus-subject',
            ),
            'body' => array(
                'class' => 'HTMLTextField',
                'label-message' => 'contactus-message',
            )
        );
        return $formDescriptor;
    }
    function onSubmit(array $data){
        die(var_dump($data));
    }
    function onSuccess(){

    }
    /**
    * Page execution.
    * @param null|string $par
    * @return void
    * @throws userBlockedError
    */
    function execute( $par ) {
        // execute must call this
        $this->setHeaders();
        $context = $this->resolve_request($par);
        if ($context['type'] == 'email' && in_array('submit', $context)) {
            $this->send_mail();
            return;
        }
        elseif($context['type'] == 'email' && !in_array('submit', $context)){
            if ($this->user->isBlocked())
                throw new userBlockedError($this->user->getBlock());
            $this->build_form('email') ;
        }
        elseif ($context['type'] == 'settings'){
            $this->checkPermissions($this->perm);
            $this->build_form('settings');

        }
    }
}

