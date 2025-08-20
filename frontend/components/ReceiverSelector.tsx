import React, { useState, useEffect } from 'react';
import { ChevronDownIcon } from '@heroicons/react/24/outline';
import { apiClient } from '@/lib/api';
import { toast } from 'react-hot-toast';

interface Faculty {
  id: number;
  name: string;
  type: string;
}

interface Class {
  id: number;
  class_name: string;
  class_code: string;
  faculty_id: number;
}

interface Student {
  id: number;
  full_name: string;
  student_code: string;
  email: string;
  class_id: number;
}

interface Lecturer {
  id: number;
  full_name: string;
  lecturer_code: string;
  email: string;
  faculty_id: number;
}

interface ReceiverSelectorProps {
  selectedReceiver: { receiver_id: number; receiver_type: 'student' | 'lecturer' | 'class' | 'all_students' } | null;
  onReceiverChange: (receiver: { receiver_id: number; receiver_type: 'student' | 'lecturer' | 'class' | 'all_students' } | null) => void;
  userType: string; // 'admin', 'lecturer', 'student'
}

export default function ReceiverSelector({ selectedReceiver, onReceiverChange, userType }: ReceiverSelectorProps) {
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [classes, setClasses] = useState<Class[]>([]);
  const [students, setStudents] = useState<Student[]>([]);
  const [lecturers, setLecturers] = useState<Lecturer[]>([]);
  
  const [selectedFaculty, setSelectedFaculty] = useState<number | null>(null);
  const [selectedClass, setSelectedClass] = useState<number | null>(null);
  const [receiverType, setReceiverType] = useState<'student' | 'lecturer' | 'class' | 'all_students'>('student');
  
  const [loadingFaculties, setLoadingFaculties] = useState(false);
  const [loadingClasses, setLoadingClasses] = useState(false);
  const [loadingStudents, setLoadingStudents] = useState(false);
  const [loadingLecturers, setLoadingLecturers] = useState(false);

  // Load faculties on component mount
  useEffect(() => {
    loadFaculties();
  }, []);

  // Load lecturers for admin
  useEffect(() => {
    if (userType === 'admin' && receiverType === 'lecturer') {
      loadLecturers();
    }
  }, [userType, receiverType]);

  const loadFaculties = async () => {
    try {
      setLoadingFaculties(true);
      const response = await apiClient.getFaculties();
      setFaculties(response.data || []);
    } catch (error) {
      console.error('Error loading faculties:', error);
      toast.error('Không thể tải danh sách khoa');
    } finally {
      setLoadingFaculties(false);
    }
  };

  const loadClassesByFaculty = async (facultyId: number) => {
    try {
      setLoadingClasses(true);
      const response = await apiClient.getClassesByFaculty(facultyId);
      setClasses(response.data || []);
    } catch (error) {
      console.error('Error loading classes:', error);
      toast.error('Không thể tải danh sách lớp');
    } finally {
      setLoadingClasses(false);
    }
  };

  const loadStudentsByClass = async (classId: number) => {
    try {
      setLoadingStudents(true);
      const response = await apiClient.getStudentsByClass(classId);
      setStudents(response.data || []);
    } catch (error) {
      console.error('Error loading students:', error);
      toast.error('Không thể tải danh sách sinh viên');
    } finally {
      setLoadingStudents(false);
    }
  };

  const loadLecturers = async () => {
    try {
      setLoadingLecturers(true);
      const response = await apiClient.getLecturers();
      setLecturers(response.data || []);
    } catch (error) {
      console.error('Error loading lecturers:', error);
      toast.error('Không thể tải danh sách giảng viên');
    } finally {
      setLoadingLecturers(false);
    }
  };

  const handleFacultyChange = (facultyId: number) => {
    setSelectedFaculty(facultyId);
    setSelectedClass(null);
    setClasses([]);
    setStudents([]);
    onReceiverChange(null);
    loadClassesByFaculty(facultyId);
  };

  const handleClassChange = (classId: number) => {
    setSelectedClass(classId);
    setStudents([]);
    onReceiverChange(null);
    if (receiverType === 'student') {
      loadStudentsByClass(classId);
    }
  };

  const handleReceiverTypeChange = (type: 'student' | 'lecturer' | 'class' | 'all_students') => {
    setReceiverType(type);
    onReceiverChange(null);
    setSelectedFaculty(null);
    setSelectedClass(null);
    setClasses([]);
    setStudents([]);
  };

  const handleStudentSelect = (student: Student) => {
    onReceiverChange({
      receiver_id: student.id,
      receiver_type: 'student'
    });
  };

  const handleLecturerSelect = (lecturer: Lecturer) => {
    onReceiverChange({
      receiver_id: lecturer.id,
      receiver_type: 'lecturer'
    });
  };

  const handleClassSelect = (classItem: Class) => {
    onReceiverChange({
      receiver_id: classItem.id,
      receiver_type: 'class'
    });
  };

  const handleAllStudentsSelect = () => {
    onReceiverChange({
      receiver_id: 0, // 0 means all students in the entire school
      receiver_type: 'all_students'
    });
  };

  return (
    <div className="space-y-4">
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">
          Loại người nhận <span className="text-red-500">*</span>
        </label>
                 <div className="flex space-x-4">
           <label className="flex items-center">
             <input
               type="radio"
               name="receiverType"
               value="student"
               checked={receiverType === 'student'}
               onChange={() => handleReceiverTypeChange('student')}
               className="mr-2"
             />
             Sinh viên
           </label>
           <label className="flex items-center">
             <input
               type="radio"
               name="receiverType"
               value="class"
               checked={receiverType === 'class'}
               onChange={() => handleReceiverTypeChange('class')}
               className="mr-2"
             />
             Lớp
           </label>
           <label className="flex items-center">
             <input
               type="radio"
               name="receiverType"
               value="all_students"
               checked={receiverType === 'all_students'}
               onChange={() => handleReceiverTypeChange('all_students')}
               className="mr-2"
             />
             Tất cả sinh viên
           </label>
           {userType === 'admin' && (
             <label className="flex items-center">
               <input
                 type="radio"
                 name="receiverType"
                 value="lecturer"
                 checked={receiverType === 'lecturer'}
                 onChange={() => handleReceiverTypeChange('lecturer')}
                 className="mr-2"
               />
               Giảng viên
             </label>
           )}
         </div>
      </div>

             {(receiverType === 'student' || receiverType === 'class') && (
        <>
          {/* Faculty Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Chọn Khoa <span className="text-red-500">*</span>
            </label>
            <div className="relative">
              <select
                value={selectedFaculty || ''}
                onChange={(e) => handleFacultyChange(Number(e.target.value))}
                className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                disabled={loadingFaculties}
              >
                <option value="">
                  {loadingFaculties ? 'Đang tải...' : 'Chọn khoa'}
                </option>
                {faculties.map((faculty) => (
                  <option key={faculty.id} value={faculty.id}>
                    {faculty.name}
                  </option>
                ))}
              </select>
              <ChevronDownIcon className="absolute right-3 top-2.5 h-5 w-5 text-gray-400 pointer-events-none" />
            </div>
          </div>

          {/* Class Selector */}
          {selectedFaculty && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Chọn Lớp <span className="text-red-500">*</span>
              </label>
              <div className="relative">
                <select
                  value={selectedClass || ''}
                  onChange={(e) => handleClassChange(Number(e.target.value))}
                  className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  disabled={loadingClasses}
                >
                  <option value="">
                    {loadingClasses ? 'Đang tải...' : 'Chọn lớp'}
                  </option>
                  {classes.map((classItem) => (
                    <option key={classItem.id} value={classItem.id}>
                      {classItem.class_name} ({classItem.class_code})
                    </option>
                  ))}
                </select>
                <ChevronDownIcon className="absolute right-3 top-2.5 h-5 w-5 text-gray-400 pointer-events-none" />
              </div>
            </div>
          )}

                     {/* Student Selector (only for student type) */}
           {receiverType === 'student' && selectedClass && students.length > 0 && (
             <div>
               <label className="block text-sm font-medium text-gray-700 mb-2">
                 Chọn Sinh viên <span className="text-red-500">*</span>
               </label>
               <div className="border border-gray-300 rounded-md max-h-48 overflow-y-auto">
                 {loadingStudents ? (
                   <div className="p-4 text-center text-gray-500">Đang tải...</div>
                 ) : (
                   <div className="divide-y divide-gray-200">
                     {students.map((student) => (
                       <div
                         key={student.id}
                         className={`p-3 cursor-pointer hover:bg-gray-50 ${
                           selectedReceiver?.receiver_id === student.id ? 'bg-blue-50 border-blue-500' : ''
                         }`}
                         onClick={() => handleStudentSelect(student)}
                       >
                         <div className="flex items-center">
                           <input
                             type="radio"
                             name="selectedStudent"
                             checked={selectedReceiver?.receiver_id === student.id}
                             onChange={() => handleStudentSelect(student)}
                             className="mr-3"
                           />
                           <div>
                             <div className="font-medium text-gray-900">{student.full_name}</div>
                             <div className="text-sm text-gray-500">
                               {student.student_code} • {student.email}
                             </div>
                           </div>
                         </div>
                       </div>
                     ))}
                   </div>
                 )}
               </div>
             </div>
           )}

           {/* Class Selector (only for class type) */}
           {receiverType === 'class' && selectedClass && (
             <div>
               <label className="block text-sm font-medium text-gray-700 mb-2">
                 Chọn Lớp <span className="text-red-500">*</span>
               </label>
               <div className="border border-gray-300 rounded-md p-3">
                 <div className="flex items-center">
                   <input
                     type="radio"
                     name="selectedClass"
                     checked={selectedReceiver?.receiver_id === selectedClass}
                     onChange={() => handleClassSelect(classes.find(c => c.id === selectedClass)!)}
                     className="mr-3"
                   />
                   <div>
                     <div className="font-medium text-gray-900">
                       {classes.find(c => c.id === selectedClass)?.class_name}
                     </div>
                     <div className="text-sm text-gray-500">
                       {classes.find(c => c.id === selectedClass)?.class_code}
                     </div>
                   </div>
                 </div>
               </div>
             </div>
           )}
        </>
      )}

                           {/* All Students Selector */}
        {receiverType === 'all_students' && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Tất cả sinh viên toàn trường <span className="text-red-500">*</span>
            </label>
            <div className="border border-gray-300 rounded-md p-3">
              <div className="flex items-center">
                <input
                  type="radio"
                  name="selectedAllStudents"
                  checked={selectedReceiver?.receiver_type === 'all_students'}
                  onChange={handleAllStudentsSelect}
                  className="mr-3"
                />
                <div>
                  <div className="font-medium text-gray-900">
                    Tất cả sinh viên toàn trường
                  </div>
                  <div className="text-sm text-gray-500">
                    Sẽ giao task cho tất cả sinh viên trong toàn trường
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

       {/* Lecturer Selector (for admin only) */}
       {receiverType === 'lecturer' && userType === 'admin' && (
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Chọn Giảng viên <span className="text-red-500">*</span>
          </label>
          <div className="border border-gray-300 rounded-md max-h-48 overflow-y-auto">
            {loadingLecturers ? (
              <div className="p-4 text-center text-gray-500">Đang tải...</div>
            ) : (
              <div className="divide-y divide-gray-200">
                {lecturers.map((lecturer) => (
                  <div
                    key={lecturer.id}
                    className={`p-3 cursor-pointer hover:bg-gray-50 ${
                      selectedReceiver?.receiver_id === lecturer.id ? 'bg-blue-50 border-blue-500' : ''
                    }`}
                    onClick={() => handleLecturerSelect(lecturer)}
                  >
                    <div className="flex items-center">
                      <input
                        type="radio"
                        name="selectedLecturer"
                        checked={selectedReceiver?.receiver_id === lecturer.id}
                        onChange={() => handleLecturerSelect(lecturer)}
                        className="mr-3"
                      />
                      <div>
                        <div className="font-medium text-gray-900">{lecturer.full_name}</div>
                        <div className="text-sm text-gray-500">
                          {lecturer.lecturer_code} • {lecturer.email}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

             {selectedReceiver && (
         <div className="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
           <div className="text-sm font-medium text-green-800">
             Đã chọn: {
               selectedReceiver.receiver_type === 'student' ? 'Sinh viên' :
               selectedReceiver.receiver_type === 'lecturer' ? 'Giảng viên' :
               selectedReceiver.receiver_type === 'class' ? 'Lớp' :
               'Tất cả sinh viên'
             }
           </div>
           <div className="text-sm text-green-600">
             ID: {selectedReceiver.receiver_id} | Type: {selectedReceiver.receiver_type}
           </div>
         </div>
       )}
    </div>
  );
}
