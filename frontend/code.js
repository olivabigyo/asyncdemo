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
// Get Messages
async function getMessages() {
    log('Downloading messages...');

    try {
        const request = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type':'application/json'
            },
            body: JSON.stringify({op: 'getMessages'})
        });
        if (!request.ok) {
            logError('Fetch failed');
            return;
        }
        if (request.status != 200) {
            logError('Fetch bad status: ' + request.status);
            return;
        }

        const data = await request.json();
        if (!data.ok) {
            logError('Server returned error: ' + data.error);
            return;
        }
        log('Messages downloaded.');

        updateMessages(data.messages);

    } catch (err) {
        console.log(err);
        logError('Exeption: ' + err);
    }
}

function updateMessages(messages) {
    for (const message of messages) {
        const newMessage = document.createElement('div');
        newMessage.innerText = message.content;
        const chatBox = document.getElementById('chatbox');
        chatBox.appendChild(newMessage);
    }
}

getMessages();
