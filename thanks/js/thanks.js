/**
 * Thanks plugin
 *
 * @package thanks
 * @author Cotonti team
 * @copyright Copyright (c) 2016-2024 Cotonti team
 * @license BSD
 */

class Thanks {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('click', (event) => {
            let addButton = event.target.closest('.thanks-add');
            if (addButton) {
                event.preventDefault();
                return this.addThankButtonHandler(addButton);
            }
        })
    }

    addThankButtonHandler(button) {
        if (button.classList.contains('confirm') && !confirm(window.thanks.confirmPrompt)) {
            return false;
        }

        let source = button.dataset.source;
        let sourceId = button.dataset.source_id;
        let toUserId = button.dataset.to_user;

        if (button.dataset.disabled === 'disabled' || !this.validateAddThank(toUserId)) {
            return false;
        }

        button.dataset.disabled = 'disabled';
        let result = this.addThank(source, sourceId)
            .then(data => {
                button.removeAttribute('data-disabled');
                if (!data) {
                    return false;
                }
                if (data.errors !== undefined && data.errors.length > 0) {
                    alert(data.errors.join('\n'));
                    return false;
                }
                if (data.whoThankedWidget !== undefined) {
                    const whoThankedContainer= document.getElementById('thanks-who-thanked-' + source + '-' + sourceId);
                    if (whoThankedContainer !== null) {
                        this.updateContainerData(whoThankedContainer, data.whoThankedWidget);
                    }
                }
                if (data.itemThanksCountWidget !== undefined) {
                    const countContainer= document.getElementById('thanks-item-count-' + source + '-' + sourceId);
                    if (countContainer !== null) {
                        this.updateContainerData(countContainer, data.itemThanksCountWidget);
                    }
                }
                if (data.userThanksCountWidget !== undefined) {
                    const userCountContainers= document.querySelectorAll('.thanks-author-thanks-' + data.userId);
                    userCountContainers.forEach(userCountContainer => {
                        this.updateContainerData(userCountContainer, data.userThanksCountWidget);
                    });
                }

                if (data.success === 1) {
                    const container = button.closest('.thanks-add-container');
                    if (container) {
                        container.innerHTML = data.message;
                        container.classList.add('thanks-thanked');
                    } else {
                        button.remove();
                    }
                }
            });
        return false;
    }

    updateContainerData(container, html) {
        if (container === undefined || container === null) {
            return;
        }

        container.style.removeProperty('display');
        container.style.opacity = '.1';
        setTimeout(() => {
            container.innerHTML = html;
            container.style.transition = 'opacity 1s ease-out';
            container.style.opacity = '1';
        }, 250);
    }

    validateAddThank(toUserId) {
        // Проверить количество благодарностей, которые пользователь уже раздал
        if (window.thanks.maxPerDay > 0 && window.thanks.maxPerDay <= window.thanks.thankedToday) {
            alert(window.thanks.errorLimit);
            return false;
        }

        if (
            window.thanks.maxToEachUser > 0
            && window.thanks.thankedUsersToday.hasOwnProperty(toUserId)
            && window.thanks.thankedUsersToday[toUserId] >= window.thanks.maxToEachUser
        ) {
            alert(window.thanks.errorUserLimit);
            return false;
        }

        return true;
    }

    async addThank(source, sourceId, button) {
        const url = 'index.php?e=thanks&a=new&_ajax=1';
        const formData = new FormData();

        formData.append('source', source);
        formData.append('item', sourceId);
        formData.append('x', window.thanks.x + '111');

        try {
            let response = await fetch(url, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                alert(window.thanks.errorRequest);
                return false;
            }

            let data = await response.json();
            if (!data) {
                // alert(window.thanks.errorRequest);
                return false;
            }
            return data;
        } catch (error) {
            alert(window.thanks.errorRequest);
        }

        return false
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Сюда еще передать количество благодарностей которые раздал пользователь всего и каждому автору
    // И соотвествие source->sourceId->автор
    // let thanks = new Thanks(thankAddButtons);
    let thanks = new Thanks();
});
