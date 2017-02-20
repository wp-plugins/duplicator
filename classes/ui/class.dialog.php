<?php

/**
 * Used to generate a thinkbox inline dialog such as an alert or confirm popup
 *
 * Standard: PSR-2
 *
 * @package SC\Dup\UI\Dialog
 *
 */
class DUP_UI_Dialog
{
    //All Dialogs
    public $title;
    public $message;
    public $width;
    public $height;
    //Confirm Only
    public $progressText;
    public $progressOn = true;
    public $jscallback;
    private $id;
    private $uniqid;

    public function __construct()
    {
        add_thickbox();
        $this->progressText = __('Processing please wait...', 'duplicator');
        $this->uniqid       = uniqid();
        $this->id           = 'dup-dlg-'.$this->uniqid;
    }

    /**
     * Gets the unique id that is assigned to each instance of a dialog
     *
     * @access public
     * @return int      The unique ID of this dialog
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Gets the unique id that is assigned to each instance of a dialogs message text
     *
     * @access public
     * @return int      The unique ID of the message
     */
    public function getMessageID()
    {
        return "{$this->id}_message";
    }

    /**
     * Initilizes the alert base html code used to display when needed
     *
     * @access public
     * @return string	The html content used for the alert dialog
     */
    public function initAlert()
    {
        $ok = __('OK', 'duplicator');

        $html = <<<HTML
		<div id="{$this->id}" style="display:none">
			<div class="dup-dlg-alert-txt">
				{$this->message}
				<br/><br/>
			</div>
			<div class="dup-dlg-alert-btns">
				<input type="button" class="button button-large" value="{$ok}" onclick="tb_remove()" />
			</div>
		</div>		
HTML;

        echo $html;
    }

    /**
     * Shows the alert base js code used to display when needed
     *
     * @access public
     * @return string	The javascript content used for the alert dialog
     */
    public function showAlert()
    {
        $this->width  = is_numeric($this->width) ? $this->width : 475;
        $this->height = is_numeric($this->height) ? $this->height : 125;

        echo "tb_show('{$this->title}', '#TB_inline?width={$this->width}&height={$this->height}&inlineId={$this->id}');";
    }

    /**
     * Shows the confirm base js code used to display when needed
     *
     * @access public
     * @return string	The javascript content used for the confirm dialog
     */
    public function initConfirm()
    {
        $ok     = __('OK', 'duplicator');
        $cancel = __('Cancel', 'duplicator');

        $progress_data  = '';
        $progress_func2 = '';

        //Enable the progress spinner
        if ($this->progressOn) {
            $progress_func1 = "__DUP_UI_Dialog_".$this->uniqid;
            $progress_func2 = ";{$progress_func1}(this)";
            $progress_data  = <<<HTML
				<div class='dup-dlg-confirm-progress'><i class='fa fa-circle-o-notch fa-spin fa-lg fa-fw'></i> {$this->progressText}</div>
				<script> 
					function {$progress_func1}(obj) 
					{
						jQuery(obj).parent().parent().find('.dup-dlg-confirm-progress').show();
						jQuery(obj).closest('.dup-dlg-confirm-btns').find('input').attr('disabled', 'true');
					}
				</script>
HTML;
        }

        $html = <<<HTML
			<div id="{$this->id}" style="display:none">
				<div class="dup-dlg-confirm-txt">
					<span id="{$this->id}_message">{$this->message}</span>
					<br/><br/>
					{$progress_data}
				</div>
				<div class="dup-dlg-confirm-btns">
					<input type="button" class="button button-large" value="{$ok}" onclick="{$this->jscallback}{$progress_func2}" />
					<input type="button" class="button button-large" value="{$cancel}" onclick="tb_remove()" />
				</div>
			</div>		
HTML;

        echo $html;
    }

    /**
     * Shows the confirm base js code used to display when needed
     *
     * @access public
     * @return string	The javascript content used for the confirm dialog
     */
    public function showConfirm()
    {
        $this->width  = is_numeric($this->width) ? $this->width : 500;
        $this->height = is_numeric($this->height) ? $this->height : 150;
        echo "tb_show('{$this->title}', '#TB_inline?width={$this->width}&height={$this->height}&inlineId={$this->id}');";
    }
}