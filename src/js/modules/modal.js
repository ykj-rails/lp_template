
export default function modal(){

  let modalOpen = document.querySelectorAll('[data-modal="open"]');
  if(!modalOpen.length) return; //存在しないところで実施しない

  let modalContainer = document.querySelector('[data-modal="container"]'); //モーダル画面全体
  for(let i=0;i<modalOpen.length;i++){
    modalOpen[i].addEventListener('click',(e)=>{
      e.preventDefault()
      modalContainer.style.display ="block";
    })
  }

  let modalClose = document.querySelector('[data-modal="close"]');
  modalClose.addEventListener('click',()=>{
    modalContainer.style.display ="none";
  })
  modalContainer.addEventListener('click',(e)=>{
    modalContainer.style.display ="none";
  })

  let modalMain = document.querySelector('[data-modal="main"]');  //モーダルの中心
  modalMain.addEventListener('click',(e)=>{
    e.stopPropagation();
  })
}

//　お問い合わせモーダル
export function contactModal() {
  const modalOpen = document.querySelectorAll('[data-modal-contact="open"]')
  if(!modalOpen.length) return
  let modalContainer = document.querySelector('[data-modal-contact="container"]'); //モーダル画面全体
  for(let i=0;i<modalOpen.length;i++){
    modalOpen[i].addEventListener('click',(e)=>{
      e.preventDefault()
      modalContainer.style.display ="block";
    })
  }

  let modalClose = document.querySelector('[data-modal-contact="close"]');
  modalClose.addEventListener('click',()=>{
    modalContainer.style.display ="none";
  })
  modalContainer.addEventListener('click',(e)=>{
    modalContainer.style.display ="none";
  })

  let modalMain = document.querySelector('[data-modal-contact="main"]');  //モーダルの中心
  modalMain.addEventListener('click',(e)=>{
    e.stopPropagation();
  })
}
