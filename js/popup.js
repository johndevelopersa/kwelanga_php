const Types = {
    /**
     * Popup types
     * @type {String}
     */
     POPUP_SUCCESS: "S",
     POPUP_INFO: "I",
     POPUP_ERROR: "E",
     POPUP_WARNING: "W",
};

const Popup = {
    lastUsedId: false,
    ALL: [],

    /**
     * Will generate a new popup based on the options passed
     * @param {{
     *      renderTo: String,
     *      type: String,
     *      id: String,
     *      header: String,
     *      message: String,
     *      other: *,
     *      mainAction: {
     *          id: String,
     *          text: String,
     *          style: String,
     *          action: Function
     *      },
     *      secondary: {
     *          id: String,
     *          text: String,
     *          style: String,
     *          action: Function
     *      }
     * }} options
     * @constructor
     */
    New: (options) => {
        if (!Popup.ALL.includes(options.id)) {
            // generate an Id for the popup
            Popup.ALL.push(options.id);

            // get the html for the popup
            const HTML = Popup.getPopupHTML({
                id: options.id,
                type: options.type,
                header: options.header,
                message: options.message,
                other: options.other,
                mainAction: {...options.mainAction, style: (options.mainAction.style)? options.mainAction.style : null},
                secondary: {...options.secondary, style: (options.secondary && options.secondary.style)? options.secondary.style : null},
            });

            // render the popup
            Popup.renderPopup({renderTo: options.renderTo, html: HTML}, (err, res) => {
                if (res === 'success') {

                    // setup the event data
                    const event = options.mainAction;

                    // assign the click events to the buttons
                    const el = document.getElementById(`customPopupMainButton-${event.id}`);

                    // if element was found apply the event
                    if (el) el.onclick = () => {
                        // Popup.Close(ID);
                        event.action();
                    };

                    // if a secondary action is supplied
                    if (options.secondary) {
                        // setup the event data
                        const secondary = options.secondary;

                        // assign the click events to the buttons
                        const el = document.getElementById(`customPopupSecondaryButton-${secondary.id}`);

                        // if element was found apply the event
                        if (el) el.onclick = () => {
                            // Popup.Close(ID);
                            secondary.action();
                        };
                    }

                }
            });
        }
        else {
            console.error(`Failed to generate Popup (${options.id}) because it already exists`);
        }
    },

    /**
     * Will generate a popup which allows you to get info from a user eg. email address
     * @param {{
     *      id: String,
     *      renderTo: String,
     *      inputs: Array<{
     *          id: any,
     *          type: String,
     *          value: String,
     *          label: String,
     *          placeholder: String,
     *      }>
     *      mainAction: {
     *          id: String,
     *          text: String,
     *          action: Function
     *      },
     *      secondary: {
     *          id: String,
     *          text: String,
     *          action: Function
     *      }
     * }} options 
     */
    GetInfo: (options) => {
        if (!Popup.ALL.includes(options.id)) {
            // generate an Id for the popup
            Popup.ALL.push(options.id);

            // get HTML
            const HTML = Popup.getInfoHTML({...options, type: Types.POPUP_INFO});
            Popup.renderPopup({renderTo: options.renderTo, html: HTML}, (res) => {
                // setup the event data
                const event = options.mainAction;

                // assign the click events to the buttons
                const el = document.getElementById(`customPopupMainButton-${event.id}`);

                // if element was found apply the event
                if (el) el.onclick = () => {
                    // Popup.Close(ID);
                    event.action();
                };

                // if a secondary action is supplied
                if (options.secondary) {
                    // setup the event data
                    const secondary = options.secondary;

                    // assign the click events to the buttons
                    const el = document.getElementById(`customPopupSecondaryButton-${secondary.id}`);

                    // if element was found apply the event
                    if (el) el.onclick = () => {
                        // Popup.Close(ID);
                        secondary.action();
                    };
                }
            });

            const returnObject = {};
            options.inputs.forEach(input => returnObject[input.id] = () => document.querySelector(`#popupInput-${input.id}`).value)
            return returnObject;
        }
        else {
            console.error(`Failed to generate Popup (${options.id}) because it already exists`);
        }
    },

    /**
     * Chooses the type of icon to be used
     * @param {String} type
     */
    iconType: (type) => {
        switch (type) {
            case Types.POPUP_SUCCESS: return '<img src="../../images/popup/success.png" alt="">';
            case Types.POPUP_INFO: return '<img src="../../images/popup/info2.png" alt="">';
            case Types.POPUP_ERROR: return '<img src="../../images/popup/error.png" alt="" style="width: 80%">';
            case Types.POPUP_WARNING: return '<img src="../../images/popup/warning.png" alt="">';
            default: return  '';
        }
    },

    /**
     * Will build up the HTML for the popup
     * @param {{
     *      id: String,
     *      type: String,
     *      header: String,
     *      message: String,
     *      other: *
     *      mainAction: {
     *          id: String,
     *          text: String,
     *          style: String,
     *          action: Function
     *      },
     *      secondary: {
     *          id: String,
     *          text: String,
     *          style: String,
     *          action: Function
     *      }
     * }} options
     */
    getPopupHTML: (options) => {

        // choose to show an image based on the type
        const image = Popup.iconType(options.type); 

        // build main button
        let buttons = `<button id="customPopupMainButton-${options.mainAction.id}" style="${options.mainAction.style}">${options.mainAction.text}</button>`;

        // if secondary action is passed build button
        if (options.secondary.id) buttons += `<button id="customPopupSecondaryButton-${options.secondary.id}" style="${options.secondary.style}">${options.secondary.text}</button>`;

        // build popup
        return `
        <div id="customPopupOuterWrapper-${options.id}" class="customPopupOuterWrapper d-flex flex-center flex-align">
            <div class="customPopup bg-white shadow-md d-flex flex-align">
                <div class="image d-flex flex-center flex-even">${image}</div>
                <div class="contentWrapper d-flex flex-col">
                    <div class="content d-flex flex-col">
                        <div class="header">${options.header}</div>
                        <div class="text text-md">${options.message}</div>
                    </div>
                    <div class="buttons d-flex flex-align flex-end">${buttons}</div>
                </div>
            </div>
        </div>`;
    },

    /**
     * Will build up the HTML for the getInfo popup
     * @param {{
     *      id: String,
     *      type: String,
     *      inputs: Array<{
     *          id: any,
     *          type: String,
     *          value: String,
     *          label: String,
     *          placeholder: String,
     *      }>
     *      mainAction: {
     *          id: String,
     *          text: String,
     *          action: Function
     *      },
     *      secondary: {
     *          id: String,
     *          text: String,
     *          action: Function
     *      }
     * }} options
     */
    getInfoHTML: (options) => {
        // choose to show an image based on the type
        const image = Popup.iconType(options.type);

        // build main button
        let buttons = `<button id="customPopupMainButton-${options.mainAction.id}">${options.mainAction.text}</button>`;

        // if secondary action is passed build button
        if (options.secondary) buttons += `<button id="customPopupSecondaryButton-${options.secondary.id}">${options.secondary.text}</button>`;

        // build up inputs
        const inputs = [];
        options.inputs.forEach(input => {
            inputs.push(`
            <div class="inputWrapper">
                <h3>${input.label}</h3>
                <input id="popupInput-${input.id}" type="${input.type}" placeholder="${input.placeholder}" value=${input.value}>
            </div>`)
        })
        
        // build popup
        return `
        <div id="customPopupOuterWrapper-${options.id}" class="customPopupOuterWrapper d-flex flex-center flex-align">
            <div class="customPopup bg-white shadow-md d-flex flex-align">
                <div class="image d-flex flex-center flex-even">${image}</div>
                <div class="contentWrapper d-flex flex-col">
                    <div class="content d-flex flex-col">${inputs.join(",")}</div>
                    <div class="buttons d-flex flex-align flex-end">${buttons}</div>
                </div>
            </div>
        </div>`;
    },

    /**
     * Renders a message to the element specified
     * @param {{renderTo: String, html: String}} options
     * @param {function, Boolean} callback
     */
    renderPopup: (options, callback=false) => {

        //* where to render message to
        const destinationEl = document.getElementById(options.renderTo);

        //* convert message html to actual html
        const parser = new DOMParser();
        const HTML_MESSAGE = parser.parseFromString(options.html, "text/html");

        if (destinationEl) {
            try {
                destinationEl.appendChild(HTML_MESSAGE.body.firstChild);
                if (callback !== false) callback(false, 'success');
            } catch (error) {
                if (callback !== false) callback(true, error);
            }
        }

    },

    /**
     * Closes a popup message
     * @param {Boolean | String} passedID
     */
    Close: (passedID=false) => {
        if (passedID !== false) {
            const wrapper = document.getElementById('customPopupOuterWrapper-'+passedID);

            // if the popup was found
            if (wrapper) {
                const message = wrapper.firstChild.nextSibling;
                //* add animation classes
                // message.classList.remove("hide");
                message.classList.add('hide');

                setTimeout(() => {
                    wrapper.classList.add('d-none');
                    wrapper.remove();
                    const idIndex = Popup.ALL.indexOf(passedID);
                    Popup.ALL.splice(idIndex, 1);
                }, 500);
            }
        }
        else {
            //* get message popup
            Popup.ALL.forEach(id => {
                const wrapper = document.getElementById('customPopupOuterWrapper-'+id);

                // if the popup was found
                if (wrapper) {
                    const message = wrapper.firstChild.nextSibling;
                    //* add animation classes
                    message.classList.remove("show");
                    message.classList.add('hide');

                    setTimeout(() => {
                        wrapper.classList.add('d-none');
                        wrapper.remove();
                        const idIndex = Popup.ALL.indexOf(id);
                        Popup.ALL.splice(idIndex, 1);
                    }, 300);
                }
            });
        }
    },

    /**
     * Closes a toast message
     * @param {String} ID
     */
    closeToastMessage: (ID) => {
        //* get message popup
        const wrapper = document.getElementById('toastMessageWrapper-'+ID);

        if (wrapper) {
            //* add animation classes
            wrapper.classList.remove("show");
            wrapper.classList.add("hide");

            setTimeout(() => {
                wrapper.classList.add('d-none');
                wrapper.remove();
            }, 500);
        }

    },

    /**
     *
     * @param {{type: String, header: String, message: String}} options
     * @constructor
     */
    LOG: (options) => {

        let style = false;
        if (options.type === Types.POPUP_ERROR) style = 'background-color: red;color: white;border-radius:' +
            ' 3px;padding: 2px 15px;';
        if (options.type === Types.POPUP_SUCCESS) style = 'background-color: yellow; color: black;border-radius:' +
            ' 3px;padding: 2px 15px;';

        console.groupCollapsed('%c '+options.header, style);
        console.log(options.message);
        console.groupEnd();

    },
};
