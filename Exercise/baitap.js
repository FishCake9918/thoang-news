const data = [];
let selectedId = null;

const nameInput = document.getElementById("name");
const yearInput = document.getElementById("year");
const countrySelect = document.getElementById("country");
const tableBody = document.getElementById("tableBody");
const searchInput = document.getElementById("search");
const totalCount = document.getElementById("totalCount");
const addBtn = document.getElementById("addBtn");
const deleteBtn = document.getElementById("deleteBtn");

function getHobbies() {
  return Array.from(document.querySelectorAll(".hobby-check:checked")).map(function (cb) {
    return cb.value;
  });
}

function clearForm() {
  nameInput.value = "";
  yearInput.value = "";
  countrySelect.value = "";
  document.querySelectorAll(".hobby-check").forEach(function (cb) {
    cb.checked = false;
  });
  nameInput.focus();
}

function updateCount(arr) {
  totalCount.textContent = "Tổng: " + arr.length;
}

function getFilteredData() {
  const keyword = searchInput.value.trim().toLowerCase();
  return data.filter(function (item) {
    return item.name.toLowerCase().includes(keyword);
  });
}

function render(arr) {
  if (!arr) {
    arr = data;
  }

  tableBody.innerHTML = "";

  if (arr.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="5" class="empty-text">Chưa có dữ liệu</td>
      </tr>
    `;
    updateCount(arr);
    return;
  }

  arr.forEach(function (item, index) {
    const row = document.createElement("tr");

    if (item.id === selectedId) {
      row.classList.add("selected-row");
    }

    row.innerHTML = `
      <td>${index + 1}</td>
      <td>${item.name}</td>
      <td>${item.year}</td>
      <td>${item.hobbies.length > 0 ? item.hobbies.join(", ") : "Không có"}</td>
      <td>${item.country || "Chưa chọn"}</td>
    `;

    row.addEventListener("click", function () {
      selectedId = item.id;
      render(getFilteredData());
    });

    tableBody.appendChild(row);
  });

  updateCount(arr);
}

addBtn.addEventListener("click", function () {
  const name = nameInput.value.trim();
  const year = yearInput.value.trim();
  const hobbies = getHobbies();
  const country = countrySelect.value;

  if (name === "") {
    alert("Vui lòng nhập họ tên");
    nameInput.focus();
    return;
  }

  if (!/^\d{4}$/.test(year)) {
    alert("Năm sinh phải gồm đúng 4 chữ số");
    yearInput.focus();
    return;
  }

  const newItem = {
    id: Date.now(),
    name: name,
    year: year,
    hobbies: hobbies,
    country: country
  };

  data.push(newItem);
  render(getFilteredData());
  clearForm();
});

deleteBtn.addEventListener("click", function () {
  if (selectedId === null) {
    alert("Vui lòng chọn một dòng để xóa");
    return;
  }

  const index = data.findIndex(function (item) {
    return item.id === selectedId;
  });

  if (index !== -1) {
    data.splice(index, 1);
  }

  selectedId = null;
  render(getFilteredData());
});

searchInput.addEventListener("input", function () {
  render(getFilteredData());
});

yearInput.addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, "").slice(0, 4);
});

render();