<?php

use Assets\Utility\StorageManager;

$this->extend('Vamshop/Core./Common/admin_edit');

$this->Html->css(array(
	'Assets.jquery.fileupload',
	'Assets.jquery.fileupload-ui',
), array(
	'block' => true,
));

$this->Vamshop->adminScript(array(
//	'Assets.fileupload/vendor/jquery.ui.widget',
	'Assets.fileupload/tmpl.min.js',
	'Assets.fileupload/load-image.all.min',
	'Assets.fileupload/canvas-to-blob.min',
	'Assets.fileupload/jquery.iframe-transport',
	'Assets.fileupload/jquery.fileupload',
	'Assets.fileupload/jquery.fileupload-process',
	'Assets.fileupload/jquery.fileupload-image',
	'Assets.fileupload/jquery.fileupload-audio',
	'Assets.fileupload/jquery.fileupload-video',
	'Assets.fileupload/jquery.fileupload-validate',
	'Assets.fileupload/jquery.fileupload-ui',
));

$indexUrl = [
	'plugin' => 'Assets',
	'controller' => 'Attachments',
	'action' => 'index'
];

if (!$this->request->query('editor')):
	$this->Breadcrumbs
		->add(__d('croogo', 'Attachments'), $indexUrl)
		->add(__d('croogo', 'Upload'), $this->request->getUri()->getPath());
endif;

if ($this->layout === 'admin_popup'):
	$this->append('title', ' ');
endif;

$formUrl = ['plugin' => 'Assets', 'controller' => 'Attachments', 'action' => 'add'];
if ($this->request->query('editor')) {
	$formUrl['editor'] = 1;
}
$this->append('form-start', $this->Form->create($attachment, [
	'url' => $formUrl,
	'type' => 'file',
	'id' => 'attachment-upload-form',
]));

$model = isset($this->request->query['model']) ? $this->request->query['model'] : null;
$foreignKey = isset($this->request->query['foreign_key']) ? $this->request->query['foreign_key'] : null;

$this->append('tab-heading');
	echo $this->Vamshop->adminTab(__d('croogo', 'Upload'), '#attachment-upload');
$this->end();

$this->append('tab-content');

	echo $this->Html->tabStart('attachment-upload');

		if (isset($model) && isset($foreignKey)):
			$assetUsage = 'asset.asset_usage.0.';
			echo $this->Form->input($assetUsage . 'model', array(
				'type' => 'hidden',
				'value' => $model,
			));
			echo $this->Form->input($assetUsage . 'foreign_key', array(
				'type' => 'hidden',
				'value' => $foreignKey,
			));
		endif;

		echo $this->element('Assets.admin/fileupload');

		if (isset($model) && isset($foreignKey)):
			echo $this->Form->input($assetUsage . 'featured_image', array(
				'type' => 'checkbox',
				'label' => 'Featured Image',
			));
		endif;

		echo $this->Form->input('asset.adapter', array(
			'type' => 'select',
			'default' => 'LocalAttachment',
			'options' => StorageManager::configured(),
		));
		echo $this->Form->input('excerpt', array(
			'label' => __d('croogo', 'Caption'),
		));
		echo $this->Form->input('title');
		echo $this->Form->input('status', array(
			'type' => 'hidden', 'value' => true,
		));
		echo $this->Form->input('asset.model', array(
			'type' => 'hidden',
			'value' => 'Attachments',
		));

	echo $this->Html->tabEnd();
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
		$this->Form->button(__d('croogo', 'Upload'), array(
			'icon' => 'upload',
			'button' => 'primary',
			'class' => 'start btn-success',
			'type' => 'submit',
			'id' => 'start_upload',
		)) .
		$this->Form->end() . ' ' .
		$this->Html->link(__d('croogo', 'Cancel'), $redirect, array(
			'class' => 'btn btn-danger',
		));
	echo $this->Html->endBox();
	echo $this->Vamshop->adminBoxes();
$this->end();

$editorMode = isset($formUrl['editor']) ? $formUrl['editor'] : 0;
$xhrUploadUrl = $this->Url->build($formUrl);
$redirectUrl = $this->Url->build($indexUrl);
$script =<<<EOF

	\$('[data-toggle=tab]:first').tab('show');
	var filesToUpload = [];
	var uploadContext = [];
	var uploadResults = [];
	var \$form = \$('#attachment-upload-form');
	\$form.fileupload({
		url: '$xhrUploadUrl',
		add: function(e, data) {
			var that = this;
			$.blueimp.fileupload.prototype.options.add.call(that, e, data)
			filesToUpload.push(data.files[0]);
			uploadContext.push(data.context);
		},
		fail: function(e, data) {
			var that = this;
			filesToUpload.pop(data.files[0])
			uploadContext.pop(data.context)
			$.blueimp.fileupload.prototype.options.fail.call(that, e, data)
		}
	});

	var \$startUpload = $('#start_upload');

	var uploadHandler = function(e) {
		var enableStartUpload = function() {
			\$startUpload
				.one('click', uploadHandler)
				.html('Upload')
				.removeAttr('disabled');
		}

		if (filesToUpload.length == 0) {
			alert('No files to upload');
			enableStartUpload();
			return false;
		}

		for (var i in filesToUpload) {
			var xhr = \$form.fileupload('send', {
				files: [filesToUpload[i]],
				context: uploadContext[i]
			})
				.then(function(result, textStatus, jqXHR) {
					uploadResults.push(result);
				})
				.catch(function(jqXHR, textStatus, errorThrown) {
					if (jqXHR.responseJSON) {
						uploadResults.push(jqXHR.responseJSON);
					} else {
						uploadResults.push(errorThrown);
					}
				});
		}

		\$startUpload.html('<i class="fa fa-spin fa-spinner"></i> Upload')
			.attr('disabled', true);

		var checkInterval = setInterval(function() {
			var uploadCount = filesToUpload.length;
			var uploadSuccess = false;
			var errorMessage = false;
			for (var i = 0; i < uploadCount; i++) {
				if (typeof uploadResults[i] !== 'undefined') {
					var errorType = typeof uploadResults[i].error;
					if (errorType !== 'undefined') {
						if (errorType === 'string') {
							uploadSuccess = false;
							errorMessage = uploadResults[i].error;
						} else {
							uploadSuccess = uploadResults[i].error === false;
						}
					}
					if (typeof uploadResults[i].message !== 'undefined') {
						errorMessage = uploadResults[i].message;
						uploadSuccess = false
					}
				}
				if (!uploadSuccess) {
					break;
				}
			}

			if (uploadSuccess) {
				clearInterval(checkInterval);
				if (uploadCount > 1) {
					alert(uploadCount + ' files uploaded successfully');
				}
				if ($editorMode == 1) {
					\$startUpload
						.removeAttr('disabled')
						.text('Close')
						.one('click', function(e) {
							window.close();
							return false;
						});
				} else {
					window.location = '$redirectUrl';
				}
			}

			if (errorMessage) {
				clearInterval(checkInterval);
				alert(errorMessage)
				filesToUpload = [];
				uploadContext = [];
				uploadResults = [];
				enableStartUpload();
			}
		}, 1000);

		return false;
	};

	\$startUpload.one('click', uploadHandler);
EOF;

$this->Js->buffer($script);