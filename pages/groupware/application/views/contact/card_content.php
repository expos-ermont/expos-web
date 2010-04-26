<?php $contact = $object; ?>
    <table width=100%><col width=250px/><col/>
    <?php if ($contact->getEmail() || $contact->getEmail2() || $contact->getEmail3()
    || is_array($im_values = $contact->getImValues()) && count($contact) || $contact->getOBirthday()) {?>
    <tr><td>
	  <?php if ($contact->getEmail() || $contact->getEmail2() || $contact->getEmail3()){ $hasEmailAddrs = true?>
	  <span style="font-weight:bold"><?php echo lang('email addresses') ?>:</span>
      <?php if ($contact->getEmail()) { ?><div style="padding-left:10px"><a href="mailto:<?php echo clean($contact->getEmail());?>"><?php echo clean($contact->getEmail());?></a></div><?php } ?>
      <?php if ($contact->getEmail2()) { ?><div style="padding-left:10px"><a href="mailto:<?php echo clean($contact->getEmail2());?>"><?php echo clean($contact->getEmail2());?></a></div><?php } ?>
      <?php if ($contact->getEmail3()) { ?><div style="padding-left:10px"><a href="mailto:<?php echo clean($contact->getEmail3());?>"><?php echo clean($contact->getEmail3());?></a></div><?php } ?>
      <?php } ?>
      <?php if ($contact->getOBirthday()) { ?><?php echo $hasEmailAddrs? '<br/>':'' ?>
      <div><span style="font-weight:bold"><?php echo lang('birthday') ?>:</span> <?php if ($contact->getOBirthday() instanceof DateTimeValue) echo clean(format_datetime($contact->getOBirthday(),user_config_option('date_format', 'd/m/Y')));?></div><?php } ?>
      </td><td>
      <?php if(is_array($im_values = $contact->getImValues()) && count($im_values)) { ?>
	  <span style="font-weight:bold"><?php echo lang('instant messaging') ?>:</span>
      <table class="imAddresses">
<?php foreach($im_values as $im_value) { ?>
<?php if($im_type = $im_value->getImType()) { ?>
        <tr>
          <td><img src="<?php echo $im_type->getIconUrl() ?>" alt="<?php echo $im_type->getName() ?>" /></td>
          <td><?php echo clean($im_value->getValue()) ?> <?php if($im_value->getIsDefault()) { ?><span class="desc">(<?php echo lang('primary im service') ?>)</span><?php } ?></td>
        </tr>
<?php } // if ?>
<?php } // foreach ?>
      </table>
<?php } // if ?>
    </td></tr>
<?php } // if ?>
    
    <?php if($contact->getWAddress() || $contact->getWCity() || $contact->getWState() || $contact->getWWebPage() || $contact->getWZipcode() || $contact->getWCountry() || $contact->getWPhoneNumber() || $contact->getWPhoneNumber2() || $contact->getWFaxNumber() || $contact->getWAssistantNumber() || $contact->getWCallbackNumber()) {?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    <?php echo lang('work'); ?>
    </div></td></tr><tr><td>
      <?php if ($contact->getFullWorkAddress()) { ?>
      	<span style="font-weight:bold"><?php echo lang('address') ?>:</span> <div style="padding-left:10px"><p><?php echo nl2br(clean($contact->getFullWorkAddress()));?></p></div><br/>
      <?php } if ($contact->getWWebPage() != '') { ?>
      	<div><span style="font-weight:bold"><?php echo lang('website') ?>:</span> <div style="padding-left:10px"><a href="<?php echo cleanUrl($contact->getWWebPage()) ?>" target="_blank" title="<?php echo lang('open this link in a new window') ?>"><?php echo clean($contact->getWWebPage()) ?></a></div></div>
      <?php } ?>
      </td><td>
      <?php if($contact->getWPhoneNumber() || $contact->getWPhoneNumber2() || $contact->getWFaxNumber() || $contact->getWAssistantNumber() || $contact->getWCallbackNumber()) {?>
    	  <span style="font-weight:bold"><?php echo lang('phone') ?>:</span>
	      <?php if ($contact->getWPhoneNumber()) { ?>
	      <div><span><?php echo lang('phone') ?>:</span> <?php echo clean($contact->getWPhoneNumber());?></div><?php } ?>
	      <?php if ($contact->getWPhoneNumber2()) { ?>
	      <div><span><?php echo lang('phone 2') ?>:</span> <?php echo clean($contact->getWPhoneNumber2());?></div><?php } ?>
	      <?php if ($contact->getWFaxNumber()) { ?>
	      <div><span><?php echo lang('fax') ?>:</span> <?php echo clean($contact->getWFaxNumber());?></div><?php } ?>
	      <?php if ($contact->getWAssistantNumber()) { ?>
	      <div><span><?php echo lang('assistant') ?>:</span> <?php echo clean($contact->getWAssistantNumber());?></div><?php } ?>
	      <?php if ($contact->getWCallbackNumber()) { ?>
	      <div><span><?php echo lang('callback') ?>:</span> <?php echo clean($contact->getWCallbackNumber());?></div><?php } ?>
      <?php } ?>
    </td></tr> 
<?php } // if ?>


    <?php if($contact->getCompany() instanceof Company){
    	$company = $contact->getCompany();?>
    <tr><td colspan=2><div style="background-position:center left;font-weight:bold; font-size:120%; color:#AAA; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('company') ?>
    </div></td></tr><tr><td colspan=2>
    	<?php
    	tpl_assign('company',$company);
    	$this->includeTemplate(get_template_path('company_card', 'company'));?>
    </td></tr> 
    <?php } ?>
    
    <?php if($contact->getHAddress() || $contact->getHCity() || $contact->getHState() || $contact->getHWebPage() || $contact->getHZipcode() || $contact->getHCountry() || $contact->getHPhoneNumber() || $contact->getHPhoneNumber2() || $contact->getHFaxNumber() || $contact->getHMobileNumber() || $contact->getHPagerNumber()) {?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('home'); ?>
    </div></td></tr><tr><td>
      <?php if ($contact->getFullHomeAddress()) { ?>
      	<span style="font-weight:bold"><?php echo lang('address') ?>:</span> <div style="padding-left:10px"><p><?php echo nl2br(clean($contact->getFullHomeAddress()));?></p></div><br/>
      <?php } if ($contact->getHWebPage() != '') { ?>
      	<div><span style="font-weight:bold"><?php echo lang('website') ?>:</span> <div style="padding-left:10px"><a href="<?php echo cleanUrl($contact->getHWebPage()) ?>" target="_blank" title="<?php echo lang('open this link in a new window') ?>"><?php echo clean($contact->getHWebPage()) ?></a></div></div>
      <?php } ?>
      </td><td>
      
      <?php if($contact->getHPhoneNumber() || $contact->getHPhoneNumber2() || $contact->getHFaxNumber() || $contact->getHMobileNumber() || $contact->getHPagerNumber()) {?>
    	  <span style="font-weight:bold"><?php echo lang('phone') ?>:</span>
	      <?php if ($contact->getHPhoneNumber()) { ?>
	      <div><span><?php echo lang('phone') ?>:</span> <?php echo clean($contact->getHPhoneNumber());?></div><?php } ?>
	      <?php if ($contact->getHPhoneNumber2()) { ?>
	      <div><span><?php echo lang('phone 2') ?>:</span> <?php echo clean($contact->getHPhoneNumber2());?></div><?php } ?>
	      <?php if ($contact->getHFaxNumber()) { ?>
	      <div><span><?php echo lang('fax') ?>:</span> <?php echo clean($contact->getHFaxNumber());?></div><?php } ?>
	      <?php if ($contact->getHMobileNumber()) { ?>
	      <div><span><?php echo lang('mobile') ?>:</span> <?php echo clean($contact->getHMobileNumber());?></div><?php } ?>
	      <?php if ($contact->getHPagerNumber()) { ?>
	      <div><span><?php echo lang('pager') ?>:</span> <?php echo clean($contact->getHPagerNumber());?></div><?php } ?>
      <?php } ?>
    </td></tr> 
<?php } // if ?>
    
    <?php if($contact->getOAddress() || $contact->getOCity() || $contact->getOState() || $contact->getOZipcode() || $contact->getOCountry() || $contact->getOPhoneNumber() || $contact->getOPhoneNumber2() || $contact->getOFaxNumber()) {?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('other'); ?>
    </div></td></tr><tr><td>
      <?php if ($contact->getFullOtherAddress()) { ?>
      	<span style="font-weight:bold"><?php echo lang('address') ?>:</span> <div style="padding-left:10px"><p><?php echo nl2br(clean($contact->getFullOtherAddress()));?></p></div><br/>
      <?php } if ($contact->getOWebPage() != '') { ?>
      <div><span style="font-weight:bold"><?php echo lang('website') ?>:</span> <div style="padding-left:10px"><a href="<?php echo cleanUrl($contact->getOWebPage()) ?>" target="_blank" title="<?php echo lang('open this link in a new window') ?>"><?php echo clean($contact->getOWebPage()) ?></a></div></div>
      <?php } ?>
      </td><td>
      
      <?php if($contact->getOPhoneNumber() || $contact->getOPhoneNumber2() || $contact->getOFaxNumber()) {?>
		<span style="font-weight:bold"><?php echo lang('phone') ?>:</span>
    	<?php if ($contact->getOPhoneNumber()) { ?>
      	<div><span><?php echo lang('phone') ?>:</span> <?php echo clean($contact->getOPhoneNumber());?></div><?php } ?>
      	<?php if ($contact->getOPhoneNumber2()) { ?>
      	<div><span><?php echo lang('phone 2') ?>:</span> <?php echo clean($contact->getOPhoneNumber2());?></div><?php } ?>
      	<?php if ($contact->getOFaxNumber()) { ?>
      	<div><span><?php echo lang('fax') ?>:</span> <?php echo clean($contact->getOFaxNumber());?></div><?php } ?>
      <?php } ?>
    </td></tr> 
<?php } // if ?>
    
    <?php
    	$roles = $contact->getRoles();
     if(!is_null($roles) && is_array($roles) && count($roles) > 0) {?>
     <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('roles'); ?>
    </div></td></tr><tr><td colspan=2>
		<table>
		<?php foreach($roles as $role){
			if($role->getProject() instanceof Project && logged_user()->isProjectUser($role->getProject())){
			?>
			<tr><td style="text-align:right">
			<?php echo '<span class="project-replace">' . $role->getProject()->getId() . '</span>'?>
      </td><td style="padding-left:10px"><div><?php echo $role->getRole()? clean($role->getRole()) : lang('n/a') ?></div></td></tr>
		<?php 	} //if
			} //foreach ?>
      		</table>
    </td></tr> 
	<?php } //if ?>
    
    <?php if ($contact->getNotes()) {?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('notes'); ?>
    </div></td></tr><tr><td colspan=2>
      <div style="padding-left:10px"><?php echo nl2br(clean($contact->getNotes())) ?></div>
    </td></tr> 
    <?php } ?>
    
    
    <?php if($contact->hasUser()){?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('assigned user'); ?>
    </div></td></tr><tr><td colspan=2>
    	<?php $user = $contact->getUser();
    	tpl_assign('user',$user);
    	$this->includeTemplate(get_template_path('user_card', 'user'));?>
    </td></tr> 
    <?php } ?>
    </table>