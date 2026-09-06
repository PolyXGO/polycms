<template>
 <div>
 <AlertDialog />
 <ConfirmDialog />
 <Modal />
 <MessageToast />
 <LandingBlockOptionsPanel />
 </div>
</template>

<script setup lang="ts">
import AlertDialog from'./AlertDialog.vue';
import ConfirmDialog from'./ConfirmDialog.vue';
import Modal from'./Modal.vue';
import MessageToast from'./MessageToast.vue';
import LandingBlockOptionsPanel from '../editor/panels/LandingBlockOptionsPanel.vue';
</script>
