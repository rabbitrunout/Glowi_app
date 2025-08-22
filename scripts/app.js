document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('modalOverlay');

  // Универсальные модалки по id
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

  if (overlay) {
    overlay.addEventListener('click', function () {
      document.querySelectorAll('.modal, .glowi-modal').forEach(m => m.style.display = 'none');
      overlay.style.display = 'none';
    });
  }
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

    // Вешаем CSS-классы по типу события
    eventClassNames: function(arg) {
      const t = arg.event.extendedProps?.eventType;
      if (t === 'competition') return ['competition-event'];
      if (t === 'training')    return ['training-event'];
      return [];
    },

    // Эмодзи в заголовок
    eventDidMount: function(info) {
      const emojiMap = {
        'schedule': '📅',
        'training': '🏋️',
        'competition': '🏆',
        'achievement': '🥇'
      };
      const prefix = emojiMap[info.event.extendedProps?.eventType] || '';
      const titleEl = info.el.querySelector('.fc-event-title');
      if (titleEl) titleEl.innerHTML = `${prefix} ${info.event.title}`;
    },

    // Добавление событий мышкой
    select: function(info) {
      const title = prompt("Название нового события:");
      if (!title) return;
      const type = prompt("Тип события: training/competition (оставьте пустым по умолчанию)");
      const eventType = (type || 'training').trim();

      const newEvent = {
        title,
        start: info.startStr,
        end: info.endStr || '',
        allDay: false,
        eventType
      };

      fetch(`add_event.php?childID=${childID}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newEvent)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          calendar.addEvent({
            id: data.id,
            title,
            start: info.startStr,
            end: info.endStr || '',
            allDay: false,
            extendedProps: { eventType } // классы подтянутся из eventClassNames
          });
        } else {
          alert('Ошибка добавления: ' + (data.error||'неизвестно'));
        }
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

  // Вынесем во внешний скоуп
  window.openModal = openModal;
  window.closeModal = closeModal;
});

/* ===== Ниже были конфликтующие функции. Переименовал, чтобы не ломать openModal(id) ===== */

function editAchievement(ach) {
  document.getElementById("editID").value = ach.achievementID;
  document.getElementById("editTitle").value = ach.title;
  document.getElementById("editType").value = ach.type;
  document.getElementById("editDate").value = ach.dateAwarded;
  document.getElementById("editPlace").value = ach.place || "";
  document.getElementById("editMedal").value = ach.medal || "none";
  openEditModal();
}

function openEditModal() {
  document.getElementById("editModal").classList.add("active");
  document.getElementById("modalOverlay").classList.add("active");
}

function closeEditModal() {
  document.getElementById("editModal").classList.remove("active");
  document.getElementById("modalOverlay").classList.remove("active");
}

document.addEventListener("click", function (e) {
  if (e.target.id === "modalOverlay") {
    closeEditModal();
  }
});

// иконки
document.addEventListener("DOMContentLoaded", () => {
  lucide.createIcons();
});

// вспомогательные модалки просмотра
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