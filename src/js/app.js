'use strict';
// import objectFitImages from 'object-fit-images';
// import MicroModal from 'micromodal';
// import console from './modules/console';
// import modal, { contactModal } from './modules/modal';
import picturefill from './libs/plugins';
// import banner from './modules/banner';
import lpForm from './modules/lp-form';
import { scrollEvt } from './modules/scroll';
// import tab from './modules/tab';

// modal();
// contactModal();
lpForm();
window.onload = () => {
  // banner();
  scrollEvt();
};
// tab();

// モーダル内ボタン
