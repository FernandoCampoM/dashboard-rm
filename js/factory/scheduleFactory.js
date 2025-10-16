/**
 * Parsea la entity a dto
 * @param {type} entity
 * @returns {toDto.scheduleCalendarFactoryAnonym$0}
 */
export function toScheduleCalendarDto(entity){
    return {
        id: entity.id,
        title: entity.title,
        dateStart: entity.start.replace("T", " ") + ":00",
        dateEnd: entity.end.replace("T", " ") + ":00",
        color: entity.color,
        employeeID: 1
    };
}