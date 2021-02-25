'use strict';

const apiEndpoint = 'http://localhost/asyncdemo/backend/server.php';

// ------------------------------------------------------------------
// Logging

const logBox = document.getElementById('logbox');

function log(entry, isError) {
    if (isError) {
        console.error(entry);
    } else {
        console.log(entry);
    }
    const newEntry = document.createElement('p');
    newEntry.innerText = entry;
    if (isError) {
        newEntry.classList.add('error');
    }
    logBox.appendChild(newEntry);
}

function logError(entry) {
    log(entry, true)
}

// log('Downloading...');
// log('Done.');
// log('Something went wrong', true);
// logError('Something went wrong');

// ------------------------------------------------------------------
// Server requests

async function serverRequest(action, payload) {
    log(`Requesting ${action} from server...`);

    try {
        const request = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action, payload })
        });
        if (request.status != 200) {
            logError('Fetch bad status: ' + request.status);
            return;
        }

        const data = await request.json();
        if (!data.ok) {
            logError('Server returned error: ' + data.error);
            return;
        }

        log(`Successful ${action} request.`);
        return data;
    } catch (err) {
        console.log(err);
        logError('Exeption: ' + err);
    }
}

async function getMessages() {
    const data = await serverRequest('getMessages');
    updateMessages(data.messages);
}

function updateMessages(messages) {
    const messageBox = document.getElementById('messages');
    messageBox.innerHTML = '';
    for (const message of messages) {
        const newMessage = document.createElement('div');

        newMessage.innerText = message.name + ': ' + message.content;
        messageBox.appendChild(newMessage);
    }
}

getMessages();
setInterval(getMessages, 2000);

// ------------------------------------------------------------------

function saveName() {
    const name = document.getElementById('name');
    localStorage.setItem('letsChatName', name.value);
}

document.getElementById('name').addEventListener('blur', saveName);

function restoreName() {
    const name = localStorage.getItem('letsChatName');
    if (!name) return;
    const nameElem = document.getElementById('name');
    nameElem.value = name;
}
// TODO: move to init()
restoreName();

// TODO: move to init()
document.getElementById('message').focus();

async function addMessage(event) {
    event.preventDefault();
    saveName();
    const name = document.getElementById('name');
    const message = document.getElementById('message');
    log(`Sending new message from ${name.value}: ${message.value}`);
    const reply = await serverRequest('addMessage', {name: name.value, content: message.value});
    if (reply) {
        log('Message successfully sent.');
        message.value = '';
        getMessages();
    }
}

document.getElementById('form').addEventListener('submit', addMessage);
