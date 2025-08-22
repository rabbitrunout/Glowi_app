
document.addEventListener('DOMContentLoaded', function () {

  const overlay = document.getElementById('modalOverlay');

  // Универсальные функции для открытия/закрытия модалок
  function openModal(id) {
    const modal = document.getElementById(id);
    if (modal && overlay) {
      modal.style.display = 'block';
      overlay.style.display = 'block';
    }
  }

  function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal && overlay) {
      modal.style.display = 'none';
      overlay.style.display = 'none';
    }
  }

  // Закрытие по клику на оверлей
  overlay.addEventListener('click', function () {
    const modals = document.querySelectorAll('.modal, .glowi-modal');
    modals.forEach(modal => modal.style.display = 'none');
    overlay.style.display = 'none';
  });

  // Закрытие модалки по крестику
  document.querySelectorAll('.modal .close, .glowi-modal .close').forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = this.closest('.modal, .glowi-modal');
      if (modal) closeModal(modal.id);
    });
  });

  // === Calendar ===
  const calendarEl = document.getElementById('calendar');
 

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'en',
    initialView: 'dayGridMonth',
    editable: true,
    selectable: true,
    eventSources: [
      fcEventsFromPHP,
      {
        url: 'get_schedule.php?childID=' + childID,
        method: 'GET',
        failure: function() { alert('Ошибка загрузки расписания'); }
      }
    ],

    eventDidMount: function(info) {
      const emojiMap = {
        'schedule': '📅',
        'training': '🏋️',
        'competition': '🏆',
        'achievement': '🥇'
      };
      const prefix = emojiMap[info.event.extendedProps.eventType] || '';
      const titleEl = info.el.querySelector('.fc-event-title');
      if (titleEl) titleEl.innerHTML = prefix + ' ' + info.event.title;
    },

    select: function(info) {
      const title = prompt("Название нового события:");
      if (!title) return;
      const type = prompt("Тип события: training/competition (оставьте пустым по умолчанию)");
      const newEvent = { title, start: info.startStr, end: info.endStr || '', allDay: false, eventType: type || 'training' };

      fetch(`add_event.php?childID=${childID}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newEvent)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          let color = '#1E90FF';
          if (newEvent.eventType === 'competition') color = '#34a853';
          calendar.addEvent({
            id: data.id,
            title,
            start: info.startStr,
            end: info.endStr || '',
            allDay: false,
            color: color,
            extendedProps: { eventType: newEvent.eventType }
          });
        } else alert('Ошибка добавления: ' + (data.error||'неизвестно'));
      });
    },

    eventClick: function(info) {
      const title = info.event.title;
      const desc = info.event.extendedProps?.description || 'Без описания';
      openModal('viewEventModal');
      document.getElementById('viewEventTitle').innerHTML = `<i data-lucide="calendar-days"></i> ${title}`;
      document.getElementById('viewEventDetails').innerText = desc;
      lucide.createIcons();
    },

    eventDrop: updateEvent,
    eventResize: updateEvent
  });

  calendar.render();

  function updateEvent(info) {
    const event = info.event || info;
    fetch('update_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: event.id,
        title: event.title,
        start: event.start.toISOString(),
        end: event.end ? event.end.toISOString() : null,
        allDay: event.allDay
      })
    })
    .then(res => res.json())
    .then(data => { if(!data.success) alert('Ошибка обновления: '+(data.error||'')) });
  }

  // === Swiper ===
  new Swiper('.swiper', {
    loop: true,
    slidesPerView: 1,
    spaceBetween: 20,
    autoplay: { delay: 3000 },
    breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
  });

  // === Lucide Icons ===
  lucide.createIcons();

  // === Кнопки для открытия модалок ===
  window.openModal = openModal;
  window.closeModal = closeModal;

});

// --- Редактирование достижений ---
function editAchievement(ach) {
  document.getElementById("editID").value = ach.achievementID;
  document.getElementById("editTitle").value = ach.title;
  document.getElementById("editType").value = ach.type;
  document.getElementById("editDate").value = ach.dateAwarded;
  document.getElementById("editPlace").value = ach.place || "";
  document.getElementById("editMedal").value = ach.medal || "none";

  openModal();
}

function openModal() {
  document.getElementById("editModal").classList.add("active");
  document.getElementById("modalOverlay").classList.add("active");
}

function closeModal() {
  document.getElementById("editModal").classList.remove("active");
  document.getElementById("modalOverlay").classList.remove("active");
}

// Закрытие по клику на оверлей
document.addEventListener("click", function (e) {
  if (e.target.id === "modalOverlay") {
    closeModal();
  }
});


// --- инициализация иконок ---
document.addEventListener("DOMContentLoaded", () => {
  lucide.createIcons();
});

 function closeGlowiModal() {
    document.getElementById("modalOverlay").style.display = "none";
    document.getElementById("viewEventModal").style.display = "none";
  }

  function openGlowiModal(title, details) {
    document.getElementById("viewEventTitle").innerHTML = `<i data-lucide="calendar-days"></i> ${title}`;
    document.getElementById("viewEventDetails").innerText = details;
    document.getElementById("modalOverlay").style.display = "block";
    document.getElementById("viewEventModal").style.display = "block";
  }
