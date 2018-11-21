<?php

$this->Vamshop->adminScript('Assets.admin');

$this->extend('Vamshop/Core./Common/admin_index');

$this->Breadcrumbs
	->add(__d('vamshop', 'Attachments'), $this->request->getUri()->getPath());

if (!empty($this->request->query)) {
	$query = $this->request->query;
} else {
	$query = array();
}

$this->append('action-buttons');

echo $this->Vamshop->adminAction(
	__d('vamshop', 'New ' . __d('vamshop', 'Attachment')),
	array_merge(array('?' => $query), array('action' => 'add')),
	array('button' => 'success')
);

$this->end();

$detailUrl = array(
	'plugin' => 'Assets',
	'controller' => 'Attachments',
	'action' => 'browse',
	'?' => array(
		'manage' => true,
	),
);

$this->append('table-heading');
	$tableHeaders = $this->Html->tableHeaders(array(
		$this->Paginator->sort('id', __d('vamshop', 'Id')),
		'&nbsp;',
		$this->Paginator->sort('title', __d('vamshop', 'Title')),
		__d('vamshop', 'Versions'),
		__d('vamshop', 'Actions'),
	));

	echo $this->Html->tag('thead', $tableHeaders);
$this->end();

$this->append('search', $this->element('Vamshop/Core.admin/search'));

$this->append('table-body');
	$rows = array();
	foreach ($attachments as $attachment) {
		$actions = array();

		$mimeType = explode('/', $attachment->asset->mime_type);
		$mimeType = $mimeType['0'];
		$assetCount = $attachment->asset_count . '&nbsp;';
		if ($mimeType == 'image') {
			$detailUrl['?']['id'] = $attachment->id;
			$actions[] = $this->Vamshop->adminRowAction('', $detailUrl, array(
				'icon' => 'suitcase',
				'data-toggle' => 'browse',
				'tooltip' => __d('assets', 'View other sizes'),
			));

			$actions[] = $this->Vamshop->adminRowActions($attachment->id);
			$resizeUrl = array_merge(
				array(
					'action' => 'resize',
					$attachment->id,
					'_ext' => 'json'
				),
				array('?' => $query)
			);

			$actions[] = $this->Vamshop->adminRowAction('', $resizeUrl, array(
				'icon' => $this->Theme->getIcon('resize'),
				'tooltip' => __d('vamshop', 'Resize this item'),
				'data-toggle' => 'resize-asset'
			));
		}

		$editUrl = array_merge(
			array('action' => 'edit', $attachment->id),
			array('?' => $query)
		);
		$actions[] = $this->Vamshop->adminRowAction('', $editUrl, array(
			'icon' => 'update',
			'tooltip' => __d('vamshop', 'Edit this item'),
		));
		$deleteUrl = array('action' => 'delete', $attachment->id);
		$deleteUrl = array_merge(array('?' => $query), $deleteUrl);
		$actions[] = $this->Vamshop->adminRowAction('', $deleteUrl, array(
			'icon' => 'delete',
			'tooltip' => __d('vamshop', 'Remove this item'),
			'escapeTitle' => false,
		), __d('vamshop', 'Are you sure?'));

		$path = $attachment->asset->path;
		switch ($mimeType) {
			case 'image':
				$imgUrl = $this->AssetsImage->resize($path, 100, 200, [
					'adapter' => $attachment->asset->adapter,
				], [
					'alt' => $attachment->title
				]);
				$thumbnail = $this->Html->link($imgUrl, $path, [
					'escape' => false,
					'data-toggle' => 'lightbox',
					'title' => $attachment['AssetsAttachment']['title'],
				]);
			break;
			case 'video':
				$thumbnail = $this->Html->media($attachment->asset->path, [
					'width' => 200,
					'controls' => true,
				]);
			break;
			default:
				$thumbnail = sprintf('%s %s (%s)',
					$this->Html->image('Vamshop/Core./img/icons/page_white.png', [
						'alt' => $mimeType,
					]),
					$mimeType,
					$this->Assets->filename2ext($attachment->asset->path)
				);
			break;
		}

		$actions = $this->Html->div('item-actions', implode(' ', $actions));

		$rows[] = array(
			$attachment->id,
			$thumbnail,
			[
				$this->Html->div(null, $attachment->title) .
				$this->Html->link(
					$this->Url->build($path, true),
					$path,
					[
						'target' => '_blank',
					]
				),
				['class' => 'title']
			],
			$assetCount,
			$actions,
		);
	}

	echo $this->Html->tableCells($rows);
$this->end();

$this->append('page-footer');
?>
<style>
	td.title {
		text-overflow: ellipsis;
		max-width: 300px;
		white-space: nowrap;
		overflow: hidden;
	}
</style>
<?php
$this->end();
