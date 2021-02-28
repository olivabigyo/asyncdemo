'use strict';

// This is the client site javascript, which the user gets from the frontend server
// in our case the github.io server

// The user load the site olivabigyo.github.io/asyncdemo/frontend
// The browser gets the index.html file from the github.io server,
//      then the bootstrap.css file from the bootstrapcdn server,
//      then the font.awesome.css file from the cdn server
//      then the style.css file from the github.io server
//      and the this code.js file too!
// You can see this in the Network Tab by inspecting the site with right click
// These are the first 5 GET requests

// The browser runs this .js file on your handy, laptop, desktop etc.
// This .js file will make requests to the backend server

// Server Endpoint
// const apiEndpoint = 'http://amongus.olivabigyo.site/asyncdemo.php';
// Local Endpoint while developing
const apiEndpoint = 'http://localhost/asyncdemo/backend/server.php';


// ------------------------------------------------------------------
// Logging
// -------


const logBox = document.getElementById('logs');

function log(entry, isError) {

    // console.logs while developing...
    // if (isError) {
    //     console.error(entry);
    // } else {
    //     console.log(entry);
    // }

    // create p for new entry
    const newEntry = document.createElement('p');
    // populate with entry content
    newEntry.innerText = entry;
    // display error entry with different style (red color)
    if (isError) {
        newEntry.classList.add('error');
    }
    // place it
    logBox.appendChild(newEntry);
    // We want to see the last logs, scroll down
    logBox.scrollTop = logBox.scrollHeight;
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
        // Error handling
        console.log(err);
        logError('Exeption: ' + err);
    }
}

// ------------------------------------------------------------------
// Update chat messages
// --------------------

async function getMessages() {
    const data = await serverRequest('getMessages');
    updateMessages(data.messages);
}

function updateMessages(messages) {
    const messageBox = document.getElementById('messages');
    messageBox.innerHTML = '';
    for (const message of messages) {
        const newMessage = document.createElement('div');
        newMessage.classList.add('message');
        newMessage.innerText = message.name + ': ' + message.content;
        messageBox.appendChild(newMessage);
    }
    // We have a fix height box for the messages and
    // we want to see the newest ones, which are at the bottom
    messageBox.scrollTop = messageBox.scrollHeight;
}

getMessages();
// We want to update in every 5 seconds
setInterval(getMessages, 5000);


// ------------------------------------------------------------------
// Submit the message
// ----------------------

async function addMessage(event) {
    // async function, we don't want to reload the site
    event.preventDefault();
    // save name in localstorage
    // yes, this is a tiny app without login etc.
    saveName();
    const name = document.getElementById('name');
    const message = document.getElementById('message');
    // we log everything to the logs panel to learn and understand more :)
    log(`Sending new message from ${name.value}: ${message.value}`);
    // this is our async request
    const reply = await serverRequest('addMessage', {name: name.value, content: message.value});
    if (reply) {
        log('Message successfully sent.');
        message.value = '';
        // we repopulate the site and don't wait for the automatic repopulation which only happens in every 2 seconds
        getMessages();
    }
}

// ------------------------------------------------------------------
// Save name in localStorage
// -------------------------

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

document.getElementById('form').addEventListener('submit', addMessage);
