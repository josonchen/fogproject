<?php
require('../commons/base.inc.php');
try {
    $Host = $FOGCore->getHostItem(false);
    $Task = $Host->get('task');
    if (!$Task || !$Task->isValid()) throw new Exception(sprintf('%s: %s (%s)', _('No Active Task found for Host'), $Host->get('name'),$Host->get('mac')->__toString()));
    $TaskType = FOGCore::getClass('TaskType',$Task->get('typeID'));
    $StorageGroup = $Task->getStorageGroup();
    if (!$StorageGroup->isValid()) throw new Exception(_('Invalid Storage Group'));
    $StorageNode = $StorageGroup->getMasterStorageNode();
    if (!$StorageNode) throw new Exception(_('Could not find a Storage Node. Is there one enabled within this Storage Group?'));
    $Image = $Task->getImage();
    $ImageName = $Image->get('name');
    $macftp = strtolower(str_replace(array(':','-','.'),'',$_REQUEST['mac']));
    $src = sprintf('%s/dev/%s',$StorageNode->get('ftppath'),$macftp);
    $dest = sprintf('%s/%s',$StorageNode->get('ftppath'),$Image->get('path'));
    $FOGFTP->set('host',$StorageNode->get('ip'))
        ->set('username',$StorageNode->get('user'))
        ->set('password',$StorageNode->get('pass'))
        ->connect()
        ->delete($dest)
        ->rename($src,$dest);
    in_array($_REQUEST['osid'],array(1,2)) ? $FOGFTP->delete(sprintf('%s/dev/%s',$StorageNode->get('ftppath'),$macftp)) : null;
    $FOGFTP->chmod(0755,$dest);
    $FOGFTP->close();
    if ($Image->get('format') == 1) $Image->set('format',0)->save();
    $Task->set('stateID',$FOGCore->getCompleteState())->set('pct','100')->set('percent','100');
    if (!$Task->save()) throw new Exception(_('Failed to update Task'));
    $EventManager->notify('HOST_IMAGEUP_COMPLETE', array(HostName=>$Host->get('name')));
    $id = @max(FOGCore::getSubObjectIDs('ImagingLog',array('hostID'=>$Host->get('id'))));
    $Image->set('deployed',$FOGCore->formatTime('now','Y-m-d H:i:s'))->save();
    FOGCore::getClass('ImagingLog',$id)
        ->set('taskID',$Task->get('id'))
        ->set('taskStateID',$Task->get('stateID'))
        ->set('createdTime',$Task->get('createdTime'))
        ->set('createdBy',$Task->get('createdBy'))
        ->set('finish',$FOGCore->formatTime('now','Y-m-d H:i:s'))
        ->save();
    FOGCore::getClass('TaskLog',$Task)
        ->set('taskID',$Task->get('id'))
        ->set('taskStateID',$Task->get('stateID'))
        ->set('createdTime',$Task->get('createdTime'))
        ->set('createdBy',$Task->get('createdBy'))
        ->save();
    echo '##';
} catch (Exception $e) {
    echo $e->getMessage();
}
