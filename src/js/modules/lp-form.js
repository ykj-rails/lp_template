import promise from 'es6-promise';
import 'whatwg-fetch';

promise.polyfill();

export default function lpForm() {
  const confirmModal = document.getElementById('confirmModal');
  const confirmBtn = document.querySelector('[data-form="confirm"]');
  const submitBtn = document.querySelector('[data-form="submit"]');
  const cancelBtn = document.querySelector('[data-form="cancel"]');
  let confirmTxt = document.querySelectorAll('[data-confirm]');
  if (!submitBtn) return;
  const submitType = 'contact';
  let validateApiUrl;
  let sendApiUrl;
  if (NODE_ENV === 'pro') {
    validateApiUrl = '/api/' + submitType + '/validate';
    sendApiUrl = '/api/' + submitType + '/send';
  } else if (NODE_ENV === 'dev') {
    validateApiUrl = '/hajime-telework/api/' + submitType + '/validate';
    sendApiUrl = '/hajime-telework/api/' + submitType + '/send';
  } else {
    console.log('開発環境変数のエラー');
  }
  const formData = {};

  // 確認ボタン押下
  confirmBtn.addEventListener('click', e => {
    e.preventDefault(); //リンクが飛ばないようにする

    //フォームデータ取得
    const sendData = new FormData();
    sendData.append('key', 'S8PMa9Db3Edm3PE3TGywMqRhfaG6YmkMexh');

    //inputタグ（checkbox以外）
    const inputs = document.querySelectorAll('input');
    for (let i = 0; i < inputs.length; i++) {
      if (inputs[i].type === 'submit' || inputs[i].type === 'checkbox') continue; //送信ボタンは含めない
      if (inputs[i].type === 'radio') {
        if (inputs[i].checked) {
          sendData.append([inputs[i].name], inputs[i].value);
          formData[inputs[i].name] = inputs[i].value;
        }
      } else {
        sendData.append([inputs[i].name], inputs[i].value);
        formData[inputs[i].name] = inputs[i].value;
      }
    }

    // checkbox
    const checkboxGroups = document.querySelectorAll('[data-validate-checkgroup]');
    for (let i = 0; i < checkboxGroups.length; i++) {
      const key = checkboxGroups[i].getAttribute('data-validate-checkgroup');
      const checkboxes = document.querySelectorAll('input[name="' + key + '[]"]');
      const arr = []
      checkboxes.forEach(el => {
        if (el.checked) {
          arr.push(el.value)
          sendData.append(key + '[]', el.value);
        }
      })
      // sendData.append([key], arr);
      formData[key] = arr
      // console.log(sendData.get([key]))
    }

    // select
    const selects = document.querySelectorAll('select'); //selectタグ
    for (let i = 0; i < selects.length; i++) {
      sendData.append([selects[i].name], selects[i].value);
      formData[selects[i].name] = selects[i].value;
    }

    // textarea
    const textarea = document.querySelectorAll('textarea'); //textareaタグ
    for (let i = 0; i < textarea.length; i++) {
      sendData.append([textarea[i].name], textarea[i].value);
      formData[textarea[i].name] = textarea[i].value;
    }
    // console.log(formData);
    // Object.keys(formData).forEach(v => {
    //   console.log(v);
    //   console.log(formData[v]);
    // });
    // Object.keys(formData).forEach(v => {
    //   // console.log(v);
    //   // console.log(formData.v);
    //   confirmTxt.forEach(d => {
    //     if (d.getAttribute('data-confirm') === v) {
    //       d.innerText = formData[v];
    //     }
    //   });
    // });

    fetchValidateApi(sendData);
  });
  // 送信ボタン押下
  submitBtn.addEventListener('click', e => {
    e.preventDefault();

    // todo ここ確認と同じ処理書いちゃってるので共通化すべし！
    const sendData = new FormData();
    sendData.append('key', 'S8PMa9Db3Edm3PE3TGywMqRhfaG6YmkMexh');
    const inputs = document.querySelectorAll('input'); //inputタグ
    for (let i = 0; i < inputs.length; i++) {
      if (inputs[i].type === 'submit' || inputs[i].type === 'checkbox') continue; //送信ボタンは含めない
      if (inputs[i].type === 'radio') {
        if (inputs[i].checked) {
          sendData.append([inputs[i].name], inputs[i].value);
        }
      } else {
        sendData.append([inputs[i].name], inputs[i].value);
      }
    }

    const checkboxGroups = document.querySelectorAll('[data-validate-checkgroup]');
    for (let i = 0; i < checkboxGroups.length; i++) {
      const key = checkboxGroups[i].getAttribute('data-validate-checkgroup');
      const checkboxes = document.querySelectorAll('input[name="' + key + '[]"]');
      checkboxes.forEach(el => {
        if (el.checked) {
          sendData.append(key + '[]', el.value);
        }
      })
    }

    const selects = document.querySelectorAll('select'); //selectタグ
    for (let i = 0; i < selects.length; i++) {
      sendData.append([selects[i].name], selects[i].value);
    }
    const textarea = document.querySelectorAll('textarea'); //textareaタグ
    for (let i = 0; i < textarea.length; i++) {
      sendData.append([textarea[i].name], textarea[i].value);
    }

    fetchSendApi(sendData);
  });
  // 修正するボタン押下
  cancelBtn.addEventListener('click', e => {
    e.preventDefault();
    confirmModal.classList.remove('is-open');
    document.body.classList.remove('is-fixed');
  });

  // 送信系
  const fetchSendFunc = (url, sendData) =>
    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: sendData,
    }).catch(() =>
      // console.log('ネットワークエラー')
      // ここにくる時はネットワークエラー（400系や500系のエラーはcatchされない）
      alert(
        'ネットワークエラーです。インターネットに接続されていることを確認して再度送信ください。'
      )
    );
  const fetchSendData = async (url, sendData) => {
    const fetch = await fetchSendFunc(url, sendData);
    const data = await fetch.json();
    return { data };
  };
  const fetchValidateApi = async sendData => {
    // validate
    const validateResData = await fetchSendData(validateApiUrl, sendData);
    if (validateResData.data.code === 422) {
      displayError(validateResData);
    }
    if (validateResData.data.code === 200) {
      // modal開く
      Object.keys(formData).forEach(v => {
        // console.log(v);
        // console.log(formData.v);
        confirmTxt.forEach(d => {
          if (d.getAttribute('data-confirm') === v) {
            d.innerText = formData[v];
          }
        });
      });
      // confirmModal.classList.add('is-open');
      // document.body.classList.add('is-fixed');
    }
  };
  const fetchSendApi = async sendData => {
    const sendResData = await fetchSendData(sendApiUrl, sendData);
    if (NODE_ENV === 'pro') {
      if (sendResData.data.code === 200) location.href = `/complete.html`;
    } else if (NODE_ENV === 'dev') {
      location.href = `/hajime-telework/complete.html`;
    } else {
      console.log('開発環境変数エラー');
    }
  };

  // バリデーションエラー処置
  // TODO: フォームの上とかsubmitボタンの下にエラーテキストを表示させる場合は、別途調整必要！
  const displayError = errData => {
    const messages = document.querySelectorAll('[data-error]');
    for (let i = 0; i < messages.length; i++) {
      messages[i].style.display = 'none'; //メッセージ表示初期化
    }
    const errors = errData.data.errors; //エラー内容がオブジェクトで入ってる
    let errorArray = [];
    for (let key in errors) {
      if (document.querySelector('[data-error="' + key + '"]')) {
        //全部のフォームについてるわけじゃないので
        errorArray.push(key); //順序を知りたいので配列に変換

        let objMessage = document.querySelector('[data-error="' + key + '"]');
        let preMes = objMessage.getAttribute('data-mes');
        objMessage.style.display = 'block';

        // console.log(`${key}: ${errors[key][0]}`)
        // console.log(`${preMes}`)

        switch (errors[key][0]) {
          default:
          case 'required':
          case 'requiredAny':
            objMessage.textContent = preMes + 'が未入力です';
            break;
          case 'regex':
          case 'email':
            objMessage.textContent = preMes + 'の入力に誤りがあります';
            break;
        }
      }
    }
    //最初のエラーメッセージまで飛ぶ
    const errorPrimary = document.querySelector(
      '[data-error="' + errorArray[0] + '"]'
    );
    errorPrimary.scrollIntoView(true);
    scrollBy(0, -100); //微調整
  };
}

// 確認画面modal
// const confirmModal = () => {
//   const confirmModal = document.getElementById('confirmModal');
//   const confirmSection = document.getElementById('confirmSection');
//   openConfirmModal();
//   closeConfirmModal();
// };
// const openConfirmModal = (trigger, target) => {
//   trigger.addEventListener('click', () => {
//     target.classList.add('is-open');
//   });
// };
// const closeConfirmModal = (trigger, target) => {
//   trigger.addEventListener('click', () => {
//     target.classList.remove('is-open');
//   });
// };
