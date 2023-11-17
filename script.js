function getToken() {
    return document.getElementById('token-field').value;
}
// console.log(getToken());

function displayError(error) {
    const errorLi = document.createElement('li');
    errorLi.classList.add('error');
    errorLi.textContent = error;

    document.getElementById('notif-wrapper').appendChild(errorLi);
    setTimeout(() => error.remove(), 2000);
}

function displayNotif(notif) {
    const notifLi = document.createElement('li');
    notifLi.classList.add('notif');
    notifLi.textContent = notif;

    document.getElementById('notif-wrapper').appendChild(notifLi);
    setTimeout(() => notif.remove(), 2000);
}

const doneBtnArr = document.querySelectorAll('.js-done-btn');
doneBtnArr.forEach(btn => {
    btn.addEventListener('click', function(event) {
        // Check and validate data
        // console.log('done', this.dataset.id, getToken());
        const id = parseInt(this.closest('[data-id-task]').dataset.idTask);
        const token = getToken();

        if (isNaN(id) || token.length < 1) {
            displayError('Oups... un problème est survenu.');
            return;
        }

        // Send HTTP request to the server with collected datas
        fetch('api.php?action=done&id=' + id + '&token=' + getToken())
        .then(response => response.json())
        .then(data => {
            
            // An error occurs, display error message
            if (!data.result) {
                displayError(data.error);
                return;
            }

            // Update user interface
            document.querySelector(`[data-id-task="${id}"]`).remove();
        })
    })
})
// console.log(doneBtnArr);