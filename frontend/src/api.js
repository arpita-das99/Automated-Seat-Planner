const BASE_URL = "http://127.0.0.1:5008/api";

async function request(path, options = {}) {
  const res = await fetch(`${BASE_URL}${path}`, {
    headers: { "Content-Type": "application/json" },
    ...options,
  });

  let body = null;
  const text = await res.text();
  if (text) {
    body = JSON.parse(text);
  }

  if (!res.ok) {
    throw new Error(body?.error || `Request failed with status ${res.status}`);
  }
  return body;
}

// Rooms
export const listRooms = () => request("/rooms");
export const createRoom = (data) =>
  request("/rooms", { method: "POST", body: JSON.stringify(data) });
export const updateRoom = (id, data) =>
  request(`/rooms/${id}`, { method: "PUT", body: JSON.stringify(data) });
export const deleteRoom = (id) => request(`/rooms/${id}`, { method: "DELETE" });

// Courses
export const listCourses = () => request("/courses");
export const createCourse = (data) =>
  request("/courses", { method: "POST", body: JSON.stringify(data) });
export const updateCourse = (id, data) =>
  request(`/courses/${id}`, { method: "PUT", body: JSON.stringify(data) });
export const deleteCourse = (id) => request(`/courses/${id}`, { method: "DELETE" });

// Students
export const listStudents = (courseId) =>
  request(courseId ? `/students?course_id=${courseId}` : "/students");
export const createStudent = (data) =>
  request("/students", { method: "POST", body: JSON.stringify(data) });
export const bulkCreateStudents = (students) =>
  request("/students/bulk", { method: "POST", body: JSON.stringify({ students }) });
export const deleteStudent = (id) => request(`/students/${id}`, { method: "DELETE" });

// Seat plans
export const listSeatPlans = () => request("/seat-plans");
export const getSeatPlan = (id) => request(`/seat-plans/${id}`);
export const generateSeatPlan = (data) =>
  request("/seat-plans/generate", { method: "POST", body: JSON.stringify(data) });
export const deleteSeatPlan = (id) => request(`/seat-plans/${id}`, { method: "DELETE" });
