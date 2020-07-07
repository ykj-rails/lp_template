
export default function tab(){
  const triggers = document.querySelectorAll('[data-trigger]');
  if(triggers.length) {
    let currentTab = triggers[0];
    let currentSection = document.querySelector(
            `[data-target="${currentTab.getAttribute('data-trigger')}"]`);
  
    triggers.forEach(value=>{
      value.addEventListener('click', e=>{
        // 現在タブを非表示
        currentTab.className = ('campaignTabSection__tab--nonActive');
        currentSection.classList.remove('isActive');
  
        // 選択されたタブを表示
        currentTab = e.currentTarget;
        currentTab.className = ('campaignTabSection__tab--active');
        currentSection = document.querySelector(
              `[data-target="${currentTab.getAttribute('data-trigger')}"]`);
        currentSection.classList.add('isActive');
      })
    })
    currentTab.click(); //一番目のタブをデフォルト表示
  };
}
