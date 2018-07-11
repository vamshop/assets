<?php

$this->loadHelper('Vamshop/FileManager.Filemanager');

$this->extend('/Common/admin_edit');

$this->Breadcrumbs
	->add(__d('croogo', 'Attachments'), array('plugin' => 'Assets', 'controller' => 'Attachments', 'action' => 'index'))
	->add($attachment->title, $this->request->getUri()->getPath());

if ($this->layout === 'admin_popup'):
	$this->append('title', ' ');
endif;

$formUrl = array('controller' => 'Attachments', 'action' => 'edit');
if (isset($this->request->query)) {
	$formUrl = array_merge($formUrl, $this->request->query);
}

$this->append('form-start', $this->Form->create($attachment, array(
	'url' => $formUrl,
)));

$this->append('tab-heading');
	echo $this->Vamshop->adminTab(__d('croogo', 'Attachment'), '#attachment-main');
	echo $this->Vamshop->adminTabs();
$this->end();

$this->append('tab-content');
	echo $this->Html->tabStart('attachment-main');
		echo $this->Form->input('id');

		echo $this->Form->input('title', array(
			'label' => __d('croogo', 'Title'),
		));
		echo $this->Form->input('excerpt', array(
			'label' => __d('croogo', 'Excerpt'),
		));

		echo $this->Form->input('file_url', array(
			'label' => __d('croogo', 'File URL'),
			'value' => $this->Url->build($attachment->asset->path, true),
			'readonly' => 'readonly')
		);

		echo $this->Form->input('file_type', array(
			'label' => __d('croogo', 'Mime Type'),
			'value' => $attachment->asset->mime_type,
			'readonly' => 'readonly')
		);
	echo $this->Html->tabEnd();

	echo $this->Vamshop->adminTabs();
$this->end();

$this->append('panels');
	$redirect = array('action' => 'index');
	if ($this->request->session()->check('Wysiwyg.redirect')) {
		$redirect = $this->request->session()->read('Wysiwyg.redirect');
	}
	if (isset($this->request->query['model'])) {
		$redirect = array_merge(
			array('action' => 'browse'),
			array('?' => $this->request->query)
		);
	}
	echo $this->Html->beginBox(__d('croogo', 'Publishing')) .
		$this->Form->button(__d('croogo', 'Save'), [
			'class' => 'btn-outline-success',
		]) . ' ' .
		$this->Html->link(
			__d('croogo', 'Cancel'),
			$redirect,
			['class' => 'cancel btn btn-outline-danger']
		);
	echo $this->Html->endBox();

	$fileType = explode('/', $attachment->asset->mime_type);
	$fileType = $fileType['0'];
	$path = $attachment->asset->path;
	if ($fileType == 'image'):
		$imgUrl = $this->AssetsImage->resize($path, 200, 300,
			array('adapter' => $attachment->asset->adapter)
		);
	else:
		$imgUrl = $this->Html->image('Vamshop/Core./img/icons/' . $this->FileManager->mimeTypeToImage($attachment->mime_type)) . ' ' . $attachment->mime_type;
	endif;

	if (preg_match('/^image/', $attachment->asset->mime_type)):
		echo $this->Html->beginBox(__d('croogo', 'Preview')) .
			$this->Html->link($imgUrl, $attachment->asset->path, array(
				'data-toggle' => 'lightbox',
			));
		echo $this->Html->endBox();
	endif;

$this->end();

$this->append('form-end', $this->Form->end());