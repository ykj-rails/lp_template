import ResizeObserver from 'resize-observer-polyfill';
export default function banner(){

  let target = document.querySelector('[data-banner="target"]');
  if(!target) return;

  const startTrigger = document.querySelector('[data-banner="startTrigger"]');
  const spTrigger = document.querySelector('[data-banner="spTrigger"]');
  const pcTrigger = document.querySelector('[data-banner="pcTrigger"]')
  const monitored = document.querySelector('[data-monitored]');
  const baseScroll = window.pageYOffset
  const startTriggerY = startTrigger.getBoundingClientRect().top + baseScroll - screen.height
  // sp pr pc
  const isSp = window.matchMedia('(max-width: 768px)').matches;
  const thisTrigger = isSp ? spTrigger : pcTrigger;
  let triggerY = thisTrigger.getBoundingClientRect().top + baseScroll - screen.height

  if(monitored){ //タブ切り替え場所であれば
    const observer = new ResizeObserver(entries=>{    //タブ切り替えによる高さ変更を検知
      triggerY = thisTrigger.getBoundingClientRect().top + baseScroll - screen.height
    })
    observer.observe(monitored);
  }

  let timer;
  window.addEventListener('scroll',function(){
    clearTimeout(timer);
    timer = setTimeout(()=>{
      let scrollY = window.pageYOffset || window.scrollY;
      //spのスクロール分岐
      if(isSp){
        spScroll(scrollY);
      //pcのスクロール分岐
      }else{
        pcScroll(scrollY);
      }
    },20);
  })

  const spScroll = scrollY =>{
    if(scrollY<=startTriggerY){
      target.style.display = 'none';
    }else{
      target.style.display = 'block'
    }

    if(scrollY>=triggerY){
      target.style.position = 'relative';
    }else {
      target.style.position = 'fixed';
    }
  }

  const pcScroll = scrollY =>{
    if(scrollY<=startTriggerY){
      target.style.display = 'none';
    }else if(scrollY<triggerY){
      target.style.display = 'block';
    }else{
      target.style.display = 'none';
    }
  }
}
