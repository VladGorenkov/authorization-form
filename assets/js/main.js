class formHandler {
  static getElementValues(Elements) {
    Object.entries(Elements).forEach(
      ([id, element]) => (Elements[id] = element.value)
    );
    return Elements;
  }
  static getElementTexts(Elements) {
    Object.entries(Elements).forEach(
      ([id, element]) => (Elements[id] = element.innerHTML)
    );
    return Elements;
  }
  static setElementValues(Elements, Values) {
    if (JSON.stringify(Elements.keys) != JSON.stringify(Values.keys)) {
      throw new Error("Keys in Elements and Values are not equal");
    } else {
      Object.entries(Elements).forEach(
        ([id, element]) => (Elements[id].value = Values[id])
      );
    }
  }
  static setElementTexts(Elements, Values) {
    Object.entries(Elements).forEach(([id, element]) => {
      Elements[id].innerHTML = Values[id] == undefined ? "" : Values[id];
    });
  }
}
/**
 * Abstract Class clientRequestHandler
 */
class clientRequestHandler {
  constructor() {
    if (this.constructor == clientRequestHandler) {
      throw new Error("Abstract classes can't be instantiated.");
    }
    this.FormElements = {};
    this.prevFormValues = {};
    this.FormValues = {};

    this.ErrorElements = {};
    this.ErrorValues = {};
  }
  validateData() {
    throw new Error("Method 'validateData()' must be implemented.");
  }
  sendRequest() {
    throw new Error("Method 'sendRequest()' must be implemented.");
  }
}
/**
 * Abstract Class clientResponseHandler
 */
class clientResponseHandler {
  constructor() {
    if (this.constructor == clientResponseHandler) {
      throw new Error("Abstract classes can't be instantiated.");
    }
  }
  handleResponse() {
    throw new Error("Method 'handleResponse()' must be implemented.");
  }
}
class loginRequestHandler extends clientRequestHandler {
  constructor() {
    super()
    this.ErrorElements = {
          'login-error': document.getElementById('login-error'),
          'password-error': document.getElementById('password-error')
      }
}
  validateData() {
      //get new form elements
      this.FormElements = {
          'login': document.getElementById('login'),
          'password': document.getElementById('password')
      }
      //cache prev form values
      this.prevFormValues = this.FormValues
      //get new form values
      this.FormValues = formHandler.getElementValues(this.FormElements)
      //get new error elements
     
      //check login
      this.ErrorValues['login-error'] = (this.FormValues['login'] == this.prevFormValues['login']) ? 'попробуйте еще раз: ' : ''
      if (this.FormValues['login'] == '') {
          this.ErrorValues['login-error'] += 'введите логин'
      } else if (this.FormValues['login'].length <= 6) {
          this.ErrorValues['login-error'] += 'слишком короткий логин'
      } else {
          this.ErrorValues['login-error'] = ''
      }
      //check password
      this.ErrorValues['password-error'] = (this.FormValues['password'] == this.prevFormValues['password']) ? 'попробуйте еще раз: ' : ''
      if (this.FormValues['password'] == '') {
          this.ErrorValues['password-error'] += 'введите пароль'
      } else if (this.FormValues['password'].length <= 6) {
          this.ErrorValues['password-error'] += 'слишком короткий пароль'
      } else {
          this.ErrorValues['password-error'] = ''
      }
      formHandler.setElementTexts(this.ErrorElements, this.ErrorValues)
  }
  sendRequest() {
      this.validateData()
      // if ErrorValues are empty
      if (Object.values(this.ErrorValues).every(error => error == '')) {
          //function(data) called with context 
          ajax.post('./vendor/signin.php', this.FormValues, function(data) {
            console.log(data)
              loginResponseHandler.handleResponse(this.ErrorElements,JSON.parse(data))
          }.bind(this))
      }
  }
}
class loginResponseHandler extends clientResponseHandler{
  static handleResponse(ErrorElements,ErrorValues) {
      if (ErrorValues.length == 0) {
          document.location.href = '/profile.php';
      } else {
          formHandler.setElementTexts(ErrorElements, ErrorValues)
      }
  }
}
class registerRequestHandler extends clientRequestHandler {
  constructor() {
      super()
      this.ErrorElements = {
          'login-error': document.getElementById('login-error'),
          'password-error': document.getElementById('password-error'),
          'confirm-password-error': document.getElementById('confirm-password-error'),
          'email-error': document.getElementById('email-error'),
          'name-error': document.getElementById('name-error'),
      }
  }
  validateData() {
      //get new form elements
      this.FormElements = {
          'login': document.getElementById('login'),
          'password': document.getElementById('password'),
          'confirm-password': document.getElementById('confirm-password'),
          'email': document.getElementById('email'),
          'name': document.getElementById('name'),
      }
      //clear ErrorValues
      this.ErrorValues = {}
      return new Promise((res, reject) => reject()).then(() => {}, function() {
          //check login
          this.prevFormValues['login'] = this.FormValues['login']
          this.FormValues['login'] = document.getElementById('login').value

          this.ErrorValues['login-error'] = (this.FormValues['login'] == this.prevFormValues['login']) ? 'попробуйте еще раз: ' : ''
          if (this.FormValues['login'] == '') {
              this.ErrorValues['login-error'] += 'введите логин'
          } else if (this.FormValues['login'].length <= 6) {
              this.ErrorValues['login-error'] += 'слишком короткий логин'
          } else if (!/^[a-zA-Z0-9]+$/.test(this.FormValues['login'])) {
              this.ErrorValues['login-error'] += 'запрещенные символы в логине'
          } else if (/^[0-9]+$/.test(this.FormValues['login'])) {
              this.ErrorValues['login-error'] += 'логин не может состоять только из цифр'
          } else {
              this.ErrorValues['login-error'] = ''
              return Promise.reject(this)
          }
      }.bind(this)).then(() => {}, function() {
          //check password
          this.prevFormValues['password'] = this.FormValues['password']
          this.FormValues['password'] = document.getElementById('password').value

          this.ErrorValues['password-error'] = (this.FormValues['password'] == this.prevFormValues['password']) ? 'попробуйте еще раз: ' : ''
          if (this.FormValues['password'] == '') {
              this.ErrorValues['password-error'] += 'введите пароль'
          } else if (this.FormValues['password'].length <= 6) {
              this.ErrorValues['password-error'] += 'слишком короткий пароль'
          } else if (!/^[a-zA-Z0-9]+$/.test(this.FormValues['password'])) {
              this.ErrorValues['password-error'] += 'запрещенные символы в пароле'
          } else {
              this.ErrorValues['password-error'] = ''
              return Promise.reject(this)
          }
      }.bind(this)).then(() => {}, function() {
          //check confirm password
          this.prevFormValues['confirm-password'] = this.FormValues['confirm-password']
          this.FormValues['confirm-password'] = document.getElementById('confirm-password').value

          this.ErrorValues['confirm-password-error'] = (this.FormValues['confirm-password'] == this.prevFormValues['confirm-password']) && (this.FormValues['confirm-password'] != this.prevFormValues['password']) ? 'попробуйте еще раз: ' : ''
          if (this.FormValues['password'] == '') {
              this.ErrorValues['confirm-password-error'] += 'подтвердите пароль'
          } else if (this.FormValues['confirm-password'] != this.FormValues['password']) {
              this.ErrorValues['confirm-password-error'] += 'пароли не совпадают'
          } else {
              this.ErrorValues['confirm-password-error'] = ''
              return Promise.reject()
          }
      }.bind(this)).then(() => {}, function() {
          //check email
          this.prevFormValues['email'] = this.FormValues['email']
          this.FormValues['email'] = document.getElementById('email').value

          this.ErrorValues['email-error'] = (this.FormValues['email'] == this.prevFormValues['email']) ? 'попробуйте еще раз: ' : ''
          if (this.FormValues['email'] == '') {
              this.ErrorValues['email-error'] += 'введите свой email'
          } else if (!this.FormValues['email'].toLowerCase().match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)) {
              this.ErrorValues['email-error'] += 'не похоже на email'
          } else {
              this.ErrorValues['email-error'] = ''
              return Promise.reject(this)
          }
      }.bind(this)).then(() => {}, function() {
          //check name
          this.prevFormValues['name'] = this.FormValues['name']
          this.FormValues['name'] = document.getElementById('name').value

          this.ErrorValues['name-error'] = (this.FormValues['name'] == this.prevFormValues['name']) ? 'попробуйте еще раз: ' : ''
          if (this.FormValues['name'] == '') {
              this.ErrorValues['name-error'] += 'введите имя'
          } else if (this.FormValues['name'].length <= 2) {
              this.ErrorValues['name-error'] += 'слишком короткий пароль'
          } else if (!/^[a-zA-Z0-9]+$/.test(this.FormValues['name'])) {
              this.ErrorValues['name-error'] += 'запрещенные символы в пароле'
          } else if (/^[0-9]+$/.test(this.FormValues['name'])) {
              this.ErrorValues['name-error'] += 'имя не может состоять только из цифр'
          } else {
              this.ErrorValues['name-error'] = ''
              return Promise.reject(this)
          }
      }.bind(this))
  }
  sendRequest() {
      this.validateData().then(function() {formHandler.setElementTexts(this.ErrorElements, this.ErrorValues)}.bind(this), function() {
          //set ErrorValues
          console.log( this.ErrorValues)
          // if ErrorValues are empty
          if (Object.values(this.ErrorValues).every(error => error == '')) {
              delete this.FormValues['confirm-password']
              console.log(this.FormValues)
              ajax.post('./vendor/signup.php', this.FormValues, function(data) {
                  console.log(data)
                  registerResponseHandler.handleResponse(this.ErrorElements, JSON.parse(data))
              }.bind(this))
          }
      }.bind(this))
  }
}
class registerResponseHandler extends clientResponseHandler {
  static handleResponse(ErrorElements, ErrorValues) {
      if (ErrorValues.length == 0) {
          document.location.href = '/profile.php';
      } else {
          formHandler.setElementTexts(ErrorElements, ErrorValues)
      }
  }
}