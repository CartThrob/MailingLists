addon-mailing_list
=============================

This extension makes it so that you can add the customer to one or more CampaignMonitor or Mailchimp lists during checkout

 In your checkout form, you must add an input field called "custom_data[campaign_monitor]" or "custom_data[mailchimp]" with the list IDs that you want to send. You can send to multiple lists as well.

**Single list example**

	<input type="hidden" value="123" name="custom_data[campaign_monitor]" /> 

**Multiple list example**

	<input type="hidden" value="123" name="custom_data[campaign_monitor][]" /> 
	<input type="hidden" value="123" name="custom_data[campaign_monitor][]" /> 


This add-on includes module settings that need to be configured for this module to work correctly.

This is a standard EE module  which is installed & configured like other modules: 
Installation: move file to system > expressionengine > third_party 
Follow additional installation instructions here: 
[ExpresionEngine Module Installation](http://expressionengine.com/user_guide/cp/add-ons/module_manager.html)



This add-on is provided as-is at no cost with no warranty expressed or implied. Support is not included. 